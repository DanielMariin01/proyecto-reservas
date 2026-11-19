<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudCategoriaProducto;
use App\Http\Resources\App\RecursoCategoriaProducto;
use App\Models\CategoriaProducto;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoriaProductoController extends Controller
{
    /**
     * El parametro Negocio $negocio no se usa directamente en varios de
     * estos metodos, pero es necesario: Laravel solo resuelve el binding
     * implicito de {negocio:slug} cuando el metodo del controlador lo
     * tipa, y ResolverNegocioDesdeRuta depende de que ese binding ya
     * este resuelto para fijar el contexto del negocio actual.
     */
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CategoriaProducto::class);

        $categorias = CategoriaProducto::query()->withCount('productos')->orderBy('orden')->orderBy('nombre')->get();

        return RecursoCategoriaProducto::collection($categorias);
    }

    public function store(SolicitudCategoriaProducto $solicitud, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', CategoriaProducto::class);

        $categoria = CategoriaProducto::create([...$solicitud->validated(), 'negocio_id' => $negocio->id]);

        // fresh(): sin esto, si el payload no mando 'orden' (es opcional),
        // el objeto en memoria se queda con ese atributo en null aunque la
        // fila real ya tenga el default de la columna (0).
        return response()->json(['data' => new RecursoCategoriaProducto($categoria->fresh())], 201);
    }

    public function update(SolicitudCategoriaProducto $solicitud, Negocio $negocio, int $categoriaProducto): JsonResponse
    {
        $categoria = CategoriaProducto::query()->findOrFail($categoriaProducto);
        $this->authorize('update', $categoria);

        $categoria->update($solicitud->validated());

        return response()->json(['data' => new RecursoCategoriaProducto($categoria)]);
    }

    public function destroy(Negocio $negocio, int $categoriaProducto): JsonResponse
    {
        $categoria = CategoriaProducto::query()->findOrFail($categoriaProducto);
        $this->authorize('delete', $categoria);

        $categoria->delete();

        return response()->json(['mensaje' => 'Categoría eliminada.']);
    }

    public function reordenar(Request $request, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', CategoriaProducto::class);

        $ids = $request->array('ids');

        foreach ($ids as $posicion => $id) {
            CategoriaProducto::query()->whereKey($id)->update(['orden' => $posicion]);
        }

        return response()->json(['mensaje' => 'Orden actualizado.']);
    }
}
