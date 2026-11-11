<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudServicio;
use App\Http\Resources\App\RecursoServicio;
use App\Models\Negocio;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

final class ServicioController extends Controller
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
        $this->authorize('viewAny', Servicio::class);

        $servicios = Servicio::query()->with('categoriaServicio')->orderBy('orden')->orderBy('nombre')->get();

        return RecursoServicio::collection($servicios);
    }

    public function store(SolicitudServicio $solicitud, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Servicio::class);

        $servicio = Servicio::create([...$solicitud->validated(), 'negocio_id' => $negocio->id]);

        return response()->json(['data' => new RecursoServicio($servicio)], 201);
    }

    public function update(SolicitudServicio $solicitud, Negocio $negocio, int $servicio): JsonResponse
    {
        $servicioModelo = Servicio::query()->findOrFail($servicio);
        $this->authorize('update', $servicioModelo);

        $servicioModelo->update($solicitud->validated());

        return response()->json(['data' => new RecursoServicio($servicioModelo)]);
    }

    public function destroy(Negocio $negocio, int $servicio): JsonResponse
    {
        $servicioModelo = Servicio::query()->findOrFail($servicio);
        $this->authorize('delete', $servicioModelo);

        $servicioModelo->delete();

        return response()->json(['mensaje' => 'Servicio eliminado.']);
    }

    public function reordenar(Request $request, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Servicio::class);

        $ids = $request->array('ids');

        foreach ($ids as $posicion => $id) {
            Servicio::query()->whereKey($id)->update(['orden' => $posicion]);
        }

        return response()->json(['mensaje' => 'Orden actualizado.']);
    }

    public function subirImagen(Request $request, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', Servicio::class);

        $request->validate([
            'imagen' => ['required', 'image', 'max:'.self::PESO_MAXIMO_IMAGEN_KB],
        ]);

        $ruta = $request->file('imagen')->store('servicios', 'public');

        abort_if($ruta === false, 500, 'No se pudo guardar la imagen.');

        return response()->json([
            'ruta' => $ruta,
            'url' => Storage::disk('public')->url($ruta),
        ]);
    }
}
