<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\CrearBloqueoRapido;
use App\Actions\GuardarHorarioSemanal;
use App\Actions\ObtenerVistaPreviaDisponibilidad;
use App\DatosTransferencia\DatosBloqueoRapido;
use App\DatosTransferencia\DatosHorarioSemanal;
use App\Enums\DiaSemana;
use App\Exceptions\RangosDeHorarioSuperpuestosException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudBloqueoRapido;
use App\Http\Requests\App\SolicitudHorarioSemanal;
use App\Http\Resources\App\RecursoBloqueo;
use App\Http\Resources\App\RecursoHorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\ServicioColumnaAgenda;
use App\Services\ServicioEstadoHorarioSemanal;
use App\Support\Fecha;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * RF-DIS-03/CU-03: autoservicio del profesional sobre su propio horario,
 * bloqueos rápidos ("no atiendo hoy de 3 a 5") y agenda visual del día,
 * sin pasar por el permiso de gestionar catálogo ni el de gestionar citas
 * todas — la autorización aquí es de identidad (el usuario tiene un
 * Profesional vinculado, y solo ve/toca el suyo), no de rol ni de permiso.
 */
final class MiHorarioController extends Controller
{
    public function __construct(
        private readonly ServicioEstadoHorarioSemanal $servicioEstadoHorarioSemanal,
        private readonly ServicioColumnaAgenda $servicioColumnaAgenda,
    ) {}

    /**
     * El parametro Negocio $negocio no se usa directamente en varios de
     * estos metodos, pero es necesario: Laravel solo resuelve el binding
     * implicito de {negocio:slug} cuando el metodo del controlador lo
     * tipa, y ResolverNegocioDesdeRuta depende de que ese binding ya
     * este resuelto para fijar el contexto del negocio actual.
     */
    public function mostrar(Negocio $negocio): JsonResponse
    {
        $profesional = $this->profesionalActual();

        return response()->json([
            'horarios' => RecursoHorarioTrabajo::collection(
                $profesional->horariosTrabajo()->orderBy('dia_semana')->orderBy('hora_inicio')->get(),
            ),
            'bloqueos' => RecursoBloqueo::collection(
                $profesional->bloqueos()->where('fin', '>=', CarbonImmutable::now())->orderBy('inicio')->get(),
            ),
        ]);
    }

    public function crearBloqueo(SolicitudBloqueoRapido $solicitud, Negocio $negocio): JsonResponse
    {
        $profesional = $this->profesionalActual();
        $datos = $solicitud->validated();

        $bloqueo = app(CrearBloqueoRapido::class)->ejecutar(new DatosBloqueoRapido(
            profesional: $profesional,
            fecha: CarbonImmutable::parse($datos['fecha']),
            horaInicio: $datos['hora_inicio'],
            horaFin: $datos['hora_fin'],
            motivo: $datos['motivo'] ?? null,
        ));

        return response()->json(['data' => new RecursoBloqueo($bloqueo)], 201);
    }

    public function eliminarBloqueo(Negocio $negocio, int $bloqueo): JsonResponse
    {
        $profesional = $this->profesionalActual();
        $profesional->bloqueos()->whereKey($bloqueo)->delete();

        return response()->json(['mensaje' => 'Bloqueo eliminado.']);
    }

    public function mostrarHorario(Negocio $negocio): JsonResponse
    {
        $profesional = $this->profesionalActual();

        return response()->json(['dias' => $this->servicioEstadoHorarioSemanal->agruparPorDia($profesional)]);
    }

    public function actualizarHorario(SolicitudHorarioSemanal $solicitud, Negocio $negocio): JsonResponse
    {
        $profesional = $this->profesionalActual();

        try {
            app(GuardarHorarioSemanal::class)->ejecutar(new DatosHorarioSemanal(
                profesional: $profesional,
                rangosPorDia: $this->servicioEstadoHorarioSemanal->aRangosPorDia($solicitud->validated()['dias']),
            ));
        } catch (RangosDeHorarioSuperpuestosException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['dias' => $this->servicioEstadoHorarioSemanal->agruparPorDia($profesional)]);
    }

    public function agenda(Request $request, Negocio $negocio): JsonResponse
    {
        $profesional = $this->profesionalActual();

        $fecha = $request->query('fecha');
        $fechaLocal = is_string($fecha) && $fecha !== ''
            ? CarbonImmutable::parse($fecha, $negocio->zona_horaria)
            : CarbonImmutable::now($negocio->zona_horaria);
        $fechaLocal = $fechaLocal->startOfDay();
        $diaSemana = DiaSemana::from($fechaLocal->dayOfWeek);

        $columna = $this->servicioColumnaAgenda->construir(
            $profesional,
            $diaSemana->value,
            $fechaLocal->utc(),
            $fechaLocal->endOfDay()->utc(),
            $this->usuarioActual(),
        );

        return response()->json([
            'fecha' => $fechaLocal->toDateString(),
            'fechaEtiqueta' => Fecha::formatear($fechaLocal, $negocio->zona_horaria),
            'columna' => $columna,
        ]);
    }

    public function vistaPreviaDisponibilidad(Request $request, Negocio $negocio): JsonResponse
    {
        $profesional = $this->profesionalActual();

        $datos = $request->validate([
            'servicio_id' => ['required', 'integer'],
            'fecha' => ['required', 'date'],
        ]);

        $servicio = Servicio::query()->findOrFail((int) $datos['servicio_id']);
        $fecha = CarbonImmutable::parse($datos['fecha'], $negocio->zona_horaria);

        $franjas = app(ObtenerVistaPreviaDisponibilidad::class)->ejecutar($profesional, $servicio, $fecha);

        return response()->json(['franjas' => $franjas]);
    }

    private function profesionalActual(): Profesional
    {
        $profesional = $this->usuarioActual()->profesional;

        abort_if($profesional === null, 403);

        return $profesional;
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
