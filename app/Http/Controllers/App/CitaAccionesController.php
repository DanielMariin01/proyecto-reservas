<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\AnularPago;
use App\Actions\CambiarEstadoCita;
use App\Actions\CancelarCita;
use App\Actions\RegistrarPagoDeCita;
use App\Actions\ReprogramarCita;
use App\DatosTransferencia\DatosReprogramacion;
use App\Enums\EstadoCitaValor;
use App\Enums\MetodoPago;
use App\Exceptions\CitaNoCompletadaException;
use App\Exceptions\FueraDePoliticaDeCancelacionException;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\HoraYaTomadaException;
use App\Exceptions\MaximoDeReprogramacionesException;
use App\Exceptions\PagoYaAnuladoException;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudCambiarEstadoCita;
use App\Http\Requests\App\SolicitudCompletarConPago;
use App\Http\Requests\App\SolicitudMotivoCita;
use App\Http\Requests\App\SolicitudReprogramarCita;
use App\Http\Resources\App\RecursoCita;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Usuario;
use App\Services\ServicioAccionesCita;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Las 5 acciones sobre una cita puntual (completar con pago, cambiar
 * estado, reprogramar, cancelar, anular pago), compartidas entre la
 * agenda del día agrupada por profesional (AgendaDelDiaController) y la
 * lista general de citas (CitaController) — un solo lugar, para nunca
 * duplicar los mismos Actions de dominio ni las mismas reglas de
 * autorización dos veces.
 */
final class CitaAccionesController extends Controller
{
    public function __construct(private readonly ServicioAccionesCita $servicioAccionesCita) {}

    public function cambiarEstado(SolicitudCambiarEstadoCita $solicitud, Negocio $negocio, int $cita): JsonResponse
    {
        $citaModelo = Cita::query()->findOrFail($cita);
        $this->authorize('update', $citaModelo);

        try {
            app(CambiarEstadoCita::class)->ejecutar(
                $citaModelo,
                EstadoCitaValor::from($solicitud->validated()['estado']),
                $this->usuarioActual(),
            );
        } catch (TransicionDeEstadoInvalidaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoCita($citaModelo->refresh(), $this->usuarioActual(), $this->servicioAccionesCita)]);
    }

    public function completarConPago(SolicitudCompletarConPago $solicitud, Negocio $negocio, int $cita): JsonResponse
    {
        $citaModelo = Cita::query()->findOrFail($cita);
        $this->authorize('update', $citaModelo);
        $this->authorize('create', [Pago::class, $citaModelo]);

        $datos = $solicitud->validated();

        try {
            $usuario = $this->usuarioActual();
            app(CambiarEstadoCita::class)->ejecutar($citaModelo, EstadoCitaValor::Completada, $usuario);
            app(RegistrarPagoDeCita::class)->ejecutar(
                cita: $citaModelo,
                metodo: MetodoPago::from($datos['metodo']),
                valor: (int) $datos['valor'],
                propina: (int) ($datos['propina'] ?? 0),
                registradoPor: $usuario,
            );
        } catch (TransicionDeEstadoInvalidaException|CitaNoCompletadaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoCita($citaModelo->refresh(), $this->usuarioActual(), $this->servicioAccionesCita)]);
    }

    public function cancelar(SolicitudMotivoCita $solicitud, Negocio $negocio, int $cita): JsonResponse
    {
        $citaModelo = Cita::query()->findOrFail($cita);
        $this->authorize('update', $citaModelo);

        try {
            app(CancelarCita::class)->ejecutar($citaModelo, $solicitud->validated()['motivo'], $this->usuarioActual());
        } catch (TransicionDeEstadoInvalidaException|FueraDePoliticaDeCancelacionException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoCita($citaModelo->refresh(), $this->usuarioActual(), $this->servicioAccionesCita)]);
    }

    public function reprogramar(SolicitudReprogramarCita $solicitud, Negocio $negocio, int $cita): JsonResponse
    {
        $citaModelo = Cita::query()->findOrFail($cita);
        $this->authorize('update', $citaModelo);

        $datos = $solicitud->validated();
        $usuario = $this->usuarioActual();
        $nuevoInicio = CarbonImmutable::parse("{$datos['fecha']} {$datos['hora']}", $negocio->zona_horaria)->utc();

        try {
            app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion(
                cita: $citaModelo,
                nuevoInicio: $nuevoInicio,
                reprogramadaPor: $usuario,
                forzar: (bool) ($datos['forzar'] ?? false),
            ));
        } catch (TransicionDeEstadoInvalidaException|FueraDePoliticaDeCancelacionException|MaximoDeReprogramacionesException|HoraInvalidaException|HoraYaTomadaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoCita($citaModelo->refresh(), $usuario, $this->servicioAccionesCita)]);
    }

    public function anularPago(SolicitudMotivoCita $solicitud, Negocio $negocio, int $cita): JsonResponse
    {
        $citaModelo = Cita::query()->findOrFail($cita);
        $pago = $citaModelo->pagoActivo();
        abort_if($pago === null, 404);
        $this->authorize('anular', $pago);

        try {
            app(AnularPago::class)->ejecutar($pago, $solicitud->validated()['motivo'], $this->usuarioActual());
        } catch (PagoYaAnuladoException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoCita($citaModelo->refresh(), $this->usuarioActual(), $this->servicioAccionesCita)]);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }
}
