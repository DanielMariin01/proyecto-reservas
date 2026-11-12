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
use App\Enums\Permiso;
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
use App\Http\Resources\App\RecursoCitaAgenda;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Services\ServicioAccionesCita;
use App\Support\Fecha;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * RF-RES-07: la agenda visual del dueño, columnas por profesional. Las
 * acciones sobre cada cita reutilizan los mismos Actions de dominio que
 * CitaResource (Filament, aún no migrado) — nunca duplicar esa lógica.
 */
final class AgendaDelDiaController extends Controller
{
    public function __construct(private readonly ServicioAccionesCita $servicioAccionesCita) {}

    public function mostrar(Request $request, Negocio $negocio): JsonResponse
    {
        $this->autorizar();

        $fecha = $request->query('fecha');
        $fechaLocal = is_string($fecha) && $fecha !== ''
            ? CarbonImmutable::parse($fecha, $negocio->zona_horaria)
            : CarbonImmutable::now($negocio->zona_horaria);
        $fechaLocal = $fechaLocal->startOfDay();

        $inicioDiaUtc = $fechaLocal->utc();
        $finDiaUtc = $fechaLocal->endOfDay()->utc();
        $usuario = $this->usuarioActual();

        $columnas = Profesional::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Profesional $profesional): array => [
                'profesionalId' => $profesional->id,
                'profesionalNombre' => $profesional->nombre,
                'profesionalColor' => $profesional->color,
                'citas' => Cita::query()
                    ->where('profesional_id', $profesional->id)
                    ->where('inicio', '>=', $inicioDiaUtc)
                    ->where('inicio', '<', $finDiaUtc)
                    ->orderBy('inicio')
                    ->get()
                    ->map(fn (Cita $cita): RecursoCitaAgenda => new RecursoCitaAgenda($cita, $usuario, $this->servicioAccionesCita)),
            ])
            ->values();

        return response()->json([
            'fecha' => $fechaLocal->toDateString(),
            'fechaEtiqueta' => Fecha::formatear($fechaLocal, $negocio->zona_horaria),
            'columnas' => $columnas,
        ]);
    }

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

        return response()->json(['data' => new RecursoCitaAgenda($citaModelo->refresh(), $this->usuarioActual(), $this->servicioAccionesCita)]);
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

        return response()->json(['data' => new RecursoCitaAgenda($citaModelo->refresh(), $this->usuarioActual(), $this->servicioAccionesCita)]);
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

        return response()->json(['data' => new RecursoCitaAgenda($citaModelo->refresh(), $this->usuarioActual(), $this->servicioAccionesCita)]);
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

        return response()->json(['data' => new RecursoCitaAgenda($citaModelo->refresh(), $usuario, $this->servicioAccionesCita)]);
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

        return response()->json(['data' => new RecursoCitaAgenda($citaModelo->refresh(), $this->usuarioActual(), $this->servicioAccionesCita)]);
    }

    private function usuarioActual(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $usuario;
    }

    private function autorizar(): void
    {
        $usuario = Auth::guard('web')->user();

        abort_if($usuario === null, 403);
        abort_unless($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value), 403);
    }
}
