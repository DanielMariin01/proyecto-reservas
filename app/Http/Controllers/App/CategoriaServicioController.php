<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudCategoriaServicio;
use App\Http\Resources\App\RecursoCategoriaServicio;
use App\Models\CategoriaServicio;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoriaServicioController extends Controller
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
        $this->authorize('viewAny', CategoriaServicio::class);

        $categorias = CategoriaServicio::query()->withCount('servicios')->orderBy('orden')->orderBy('nombre')->get();

        return RecursoCategoriaServicio::collection($categorias);
    }

    public function store(SolicitudCategoriaServicio $solicitud, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', CategoriaServicio::class);

        $categoria = CategoriaServicio::create([...$solicitud->validated(), 'negocio_id' => $negocio->id]);

        return response()->json(['data' => new RecursoCategoriaServicio($categoria)], 201);
    }

    public function update(SolicitudCategoriaServicio $solicitud, Negocio $negocio, int $categoriaServicio): JsonResponse
    {
        $categoria = CategoriaServicio::query()->findOrFail($categoriaServicio);
        $this->authorize('update', $categoria);

        $categoria->update($solicitud->validated());

        return response()->json(['data' => new RecursoCategoriaServicio($categoria)]);
    }

    public function destroy(Negocio $negocio, int $categoriaServicio): JsonResponse
    {
        $categoria = CategoriaServicio::query()->findOrFail($categoriaServicio);
        $this->authorize('delete', $categoria);

        $categoria->delete();

        return response()->json(['mensaje' => 'Categoría eliminada.']);
    }

    public function reordenar(Request $request, Negocio $negocio): JsonResponse
    {
        $this->authorize('create', CategoriaServicio::class);

        $ids = $request->array('ids');

        foreach ($ids as $posicion => $id) {
            CategoriaServicio::query()->whereKey($id)->update(['orden' => $posicion]);
        }

        return response()->json(['mensaje' => 'Orden actualizado.']);
    }
}
