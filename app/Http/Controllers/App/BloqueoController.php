<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudBloqueo;
use App\Http\Resources\App\RecursoBloqueo;
use App\Models\Negocio;
use App\Models\Profesional;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BloqueoController extends Controller
{
    public function index(Negocio $negocio, int $profesional): AnonymousResourceCollection
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('view', $profesionalModelo);

        $bloqueos = $profesionalModelo->bloqueos()->orderByDesc('inicio')->get();

        return RecursoBloqueo::collection($bloqueos);
    }

    public function store(SolicitudBloqueo $solicitud, Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        $datos = $this->aDatosDeColumna($solicitud->validated(), $negocio);
        $bloqueo = $profesionalModelo->bloqueos()->create([...$datos, 'negocio_id' => $negocio->id]);

        return response()->json(['data' => new RecursoBloqueo($bloqueo)], 201);
    }

    public function update(SolicitudBloqueo $solicitud, Negocio $negocio, int $profesional, int $bloqueo): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        $bloqueoModelo = $profesionalModelo->bloqueos()->findOrFail($bloqueo);
        $bloqueoModelo->update($this->aDatosDeColumna($solicitud->validated(), $negocio));

        return response()->json(['data' => new RecursoBloqueo($bloqueoModelo)]);
    }

    public function destroy(Negocio $negocio, int $profesional, int $bloqueo): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        $bloqueoModelo = $profesionalModelo->bloqueos()->findOrFail($bloqueo);
        $bloqueoModelo->delete();

        return response()->json(['mensaje' => 'Bloqueo eliminado.']);
    }

    /**
     * Las fechas/horas que ve el dueño están en la zona del negocio
     * (RNF-08); aquí se combinan y se convierten a UTC antes de guardar,
     * igual que hacía el BloqueosRelationManager de Filament.
     *
     * @param  array<string, mixed>  $datos
     * @return array{tipo: string, motivo: string|null, inicio: CarbonImmutable, fin: CarbonImmutable}
     */
    private function aDatosDeColumna(array $datos, Negocio $negocio): array
    {
        $inicio = CarbonImmutable::parse("{$datos['fecha_inicio']} {$datos['hora_inicio']}", $negocio->zona_horaria)->utc();
        $fin = CarbonImmutable::parse("{$datos['fecha_fin']} {$datos['hora_fin']}", $negocio->zona_horaria)->utc();

        return [
            'tipo' => $datos['tipo'],
            'motivo' => $datos['motivo'] ?? null,
            'inicio' => $inicio,
            'fin' => $fin,
        ];
    }
}
