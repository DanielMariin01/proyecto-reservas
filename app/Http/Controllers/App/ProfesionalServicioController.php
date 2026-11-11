<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudActualizarPivoteServicio;
use App\Http\Requests\App\SolicitudAttachServicio;
use App\Http\Resources\App\RecursoServicioVinculado;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;

final class ProfesionalServicioController extends Controller
{
    public function index(Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('view', $profesionalModelo);

        $vinculados = $profesionalModelo->servicios()->orderBy('nombre')->get();
        $disponibles = Servicio::query()
            ->whereNotIn('id', $vinculados->pluck('id'))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json([
            'data' => RecursoServicioVinculado::collection($vinculados),
            'disponibles' => $disponibles,
        ]);
    }

    public function store(SolicitudAttachServicio $solicitud, Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        $datos = $solicitud->validated();

        $profesionalModelo->servicios()->attach($datos['servicio_id'], [
            'precio' => $datos['precio'] ?? null,
            'duracion_minutos' => $datos['duracion_minutos'] ?? null,
        ]);

        return response()->json(['mensaje' => 'Servicio vinculado.'], 201);
    }

    public function update(SolicitudActualizarPivoteServicio $solicitud, Negocio $negocio, int $profesional, int $servicio): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        $profesionalModelo->servicios()->updateExistingPivot($servicio, $solicitud->validated());

        return response()->json(['mensaje' => 'Servicio actualizado.']);
    }

    public function destroy(Negocio $negocio, int $profesional, int $servicio): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        $profesionalModelo->servicios()->detach($servicio);

        return response()->json(['mensaje' => 'Servicio desvinculado.']);
    }
}
