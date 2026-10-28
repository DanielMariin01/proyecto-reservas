<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publico\SolicitudDisponibilidadPublica;
use App\Http\Resources\Publico\RecursoHoraDisponible;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioDisponibilidad;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DisponibilidadController extends Controller
{
    public function __construct(
        private readonly ServicioDisponibilidad $servicioDisponibilidad,
    ) {}

    public function index(SolicitudDisponibilidadPublica $request, Negocio $negocio): JsonResponse|AnonymousResourceCollection
    {
        $servicio = Servicio::query()
            ->where('negocio_id', $negocio->id)
            ->where('visible', true)
            ->findOrFail($request->integer('servicio'));

        $profesional = $this->resolverProfesional($request, $negocio);

        if ($request->consultaPorMes()) {
            return response()->json([
                'dias' => $this->servicioDisponibilidad->obtenerDiasConDisponibilidadEnMes(
                    $negocio,
                    $servicio,
                    $profesional,
                    (string) $request->string('mes'),
                ),
            ]);
        }

        $fecha = CarbonImmutable::parse((string) $request->string('fecha'), $negocio->zona_horaria);

        $profesionales = $profesional !== null
            ? collect([$profesional])
            : $servicio->profesionales()->where('activo', true)->get();

        $horas = $this->servicioDisponibilidad->obtenerFranjasParaCualquierProfesional($profesionales, $servicio, $fecha);

        return RecursoHoraDisponible::collection($horas);
    }

    private function resolverProfesional(SolicitudDisponibilidadPublica $request, Negocio $negocio): ?Profesional
    {
        if (! $request->filled('profesional')) {
            return null;
        }

        return Profesional::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->findOrFail($request->integer('profesional'));
    }
}
