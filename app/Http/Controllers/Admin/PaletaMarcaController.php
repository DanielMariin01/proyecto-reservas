<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SolicitudPaletaMarca;
use App\Http\Resources\Admin\RecursoPaletaMarca;
use App\Models\PaletaMarca;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PaletaMarcaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $paletas = PaletaMarca::query()
            ->withCount('negocios')
            ->when(
                $request->filled('buscar'),
                fn (Builder $query): Builder => $query->where('nombre', 'like', '%'.$request->string('buscar').'%'),
            )
            ->orderBy('nombre')
            ->paginate(25);

        return RecursoPaletaMarca::collection($paletas);
    }

    public function store(SolicitudPaletaMarca $solicitud): JsonResponse
    {
        $paleta = PaletaMarca::create($solicitud->validated());

        return response()->json(['data' => new RecursoPaletaMarca($paleta)], 201);
    }

    public function update(SolicitudPaletaMarca $solicitud, PaletaMarca $paleta): JsonResponse
    {
        $paleta->update($solicitud->validated());

        return response()->json(['data' => new RecursoPaletaMarca($paleta)]);
    }

    public function destroy(PaletaMarca $paleta): JsonResponse
    {
        $paleta->delete();

        return response()->json(['mensaje' => 'Paleta eliminada.']);
    }
}
