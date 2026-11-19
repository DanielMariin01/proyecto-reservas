<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudProducto;
use App\Http\Resources\App\RecursoProducto;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

final class ProductoController extends Controller
{
    private const PESO_MAXIMO_IMAGEN_KB = 5120;

    /**
     * El parametro Negocio $negocio no se usa directamente en varios de
     * estos metodos, pero es necesario: Laravel solo resuelve el binding
     * implicito de {negocio:slug} cuando el metodo del controlador lo
     * tipa, y ResolverNegocioDesdeRuta depende de que ese binding ya
     * este resuelto para fijar el contexto del negocio actual.
     */
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Producto::class);

        $productos = Producto::query()->orderBy('orden')->orderBy('nombre')->get();

        return RecursoProducto::collection($productos);
    }

    public function store(SolicitudProducto $solicitud, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Producto::class);

        $producto = Producto::create([...$solicitud->validated(), 'negocio_id' => $negocio->id]);

        // fresh(): sin esto, si el payload no mando 'visible'/'orden' (son
        // opcionales), el objeto en memoria se queda con esos atributos en
        // null aunque la fila real en la base de datos ya tenga el default
        // de la columna (true/0).
        return response()->json(['data' => new RecursoProducto($producto->fresh())], 201);
    }

    public function update(SolicitudProducto $solicitud, Negocio $negocio, int $producto): JsonResponse
    {
        $productoModelo = Producto::query()->findOrFail($producto);
        $this->authorize('update', $productoModelo);

        $productoModelo->update($solicitud->validated());

        return response()->json(['data' => new RecursoProducto($productoModelo)]);
    }

    public function destroy(Negocio $negocio, int $producto): JsonResponse
    {
        $productoModelo = Producto::query()->findOrFail($producto);
        $this->authorize('delete', $productoModelo);

        $productoModelo->delete();

        return response()->json(['mensaje' => 'Producto eliminado.']);
    }

    public function reordenar(Request $request, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Producto::class);

        $ids = $request->array('ids');

        foreach ($ids as $posicion => $id) {
            Producto::query()->whereKey($id)->update(['orden' => $posicion]);
        }

        return response()->json(['mensaje' => 'Orden actualizado.']);
    }

    public function subirImagen(Request $request, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Producto::class);

        $request->validate([
            'imagen' => ['required', 'image', 'max:'.self::PESO_MAXIMO_IMAGEN_KB],
        ]);

        $ruta = $request->file('imagen')->store('productos', 'public');

        abort_if($ruta === false, 500, 'No se pudo guardar la imagen.');

        return response()->json([
            'ruta' => $ruta,
            'url' => Storage::disk('public')->url($ruta),
        ]);
    }
}
