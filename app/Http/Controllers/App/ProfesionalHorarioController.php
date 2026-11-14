<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\GuardarHorarioSemanal;
use App\Actions\ObtenerVistaPreviaDisponibilidad;
use App\DatosTransferencia\DatosHorarioSemanal;
use App\Exceptions\RangosDeHorarioSuperpuestosException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudHorarioSemanal;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioEstadoHorarioSemanal;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Horario semanal de un profesional, editado por el dueño desde su perfil
 * (RF-DIS-05 / CU-03). El autoservicio del propio profesional vive en
 * MiHorarioController: tiene permisos distintos (identidad, no el permiso
 * de gestionar catálogo) por eso no comparten controlador.
 */
final class ProfesionalHorarioController extends Controller
{
    public function __construct(private readonly ServicioEstadoHorarioSemanal $servicioEstadoHorarioSemanal) {}

    public function mostrar(Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('view', $profesionalModelo);

        return response()->json(['dias' => $this->servicioEstadoHorarioSemanal->agruparPorDia($profesionalModelo)]);
    }

    public function actualizar(SolicitudHorarioSemanal $solicitud, Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('update', $profesionalModelo);

        try {
            app(GuardarHorarioSemanal::class)->ejecutar(new DatosHorarioSemanal(
                profesional: $profesionalModelo,
                rangosPorDia: $this->servicioEstadoHorarioSemanal->aRangosPorDia($solicitud->validated()['dias']),
            ));
        } catch (RangosDeHorarioSuperpuestosException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['dias' => $this->servicioEstadoHorarioSemanal->agruparPorDia($profesionalModelo)]);
    }

    public function vistaPreviaDisponibilidad(Request $request, Negocio $negocio, int $profesional): JsonResponse
    {
        $profesionalModelo = Profesional::query()->findOrFail($profesional);
        $this->authorize('view', $profesionalModelo);

        $datos = $request->validate([
            'servicio_id' => ['required', 'integer'],
            'fecha' => ['required', 'date'],
        ]);

        $servicio = Servicio::query()->findOrFail((int) $datos['servicio_id']);
        $fecha = CarbonImmutable::parse($datos['fecha'], $negocio->zona_horaria);

        $franjas = app(ObtenerVistaPreviaDisponibilidad::class)->ejecutar($profesionalModelo, $servicio, $fecha);

        return response()->json(['franjas' => $franjas]);
    }
}
