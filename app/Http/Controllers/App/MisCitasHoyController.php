<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\CambiarEstadoCita;
use App\Actions\RegistrarPagoDeCita;
use App\Enums\EstadoCitaValor;
use App\Enums\MetodoPago;
use App\Exceptions\CitaNoCompletadaException;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SolicitudCompletarConPago;
use App\Http\Resources\App\RecursoCitaMisCitasHoy;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * CU-04: el profesional ve solo sus citas de hoy y cambia su estado (en
 * atención, completada con pago, no asistió). Autorización por
 * identidad (es su propio Profesional), no por PoliticaCita: esta
 * pantalla nunca muestra ni deja tocar la cita de otro profesional.
 */
final class MisCitasHoyController extends Controller
{
    public function mostrar(Negocio $negocio): JsonResponse
    {
        $profesional = $this->profesionalActual();
        $negocioActual = Negocio::findOrFail($profesional->negocio_id);
        $fechaLocal = CarbonImmutable::now($negocioActual->zona_horaria)->startOfDay();

        $citas = Cita::query()
            ->where('profesional_id', $profesional->id)
            ->where('inicio', '>=', $fechaLocal->utc())
            ->where('inicio', '<', $fechaLocal->endOfDay()->utc())
            ->orderBy('inicio')
            ->get();

        return response()->json([
            'citas' => RecursoCitaMisCitasHoy::collection($citas),
            'metodosPago' => array_map(
                fn (MetodoPago $metodo): array => ['value' => $metodo->value, 'label' => $metodo->etiqueta()],
                MetodoPago::cases(),
            ),
        ]);
    }

    public function marcarEnAtencion(Negocio $negocio, int $cita): JsonResponse
    {
        return $this->cambiarEstado($cita, EstadoCitaValor::EnAtencion);
    }

    public function marcarNoAsistio(Negocio $negocio, int $cita): JsonResponse
    {
        return $this->cambiarEstado($cita, EstadoCitaValor::NoAsistio);
    }

    public function completarConPago(SolicitudCompletarConPago $solicitud, Negocio $negocio, int $cita): JsonResponse
    {
        $citaModelo = $this->citaPropiaOFallar($cita);
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

        return response()->json(['data' => new RecursoCitaMisCitasHoy($citaModelo->refresh())]);
    }

    private function cambiarEstado(int $citaId, EstadoCitaValor $destino): JsonResponse
    {
        $citaModelo = $this->citaPropiaOFallar($citaId);

        try {
            app(CambiarEstadoCita::class)->ejecutar($citaModelo, $destino, $this->usuarioActual());
        } catch (TransicionDeEstadoInvalidaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return response()->json(['data' => new RecursoCitaMisCitasHoy($citaModelo->refresh())]);
    }

    private function citaPropiaOFallar(int $citaId): Cita
    {
        return Cita::query()->where('profesional_id', $this->profesionalActual()->id)->findOrFail($citaId);
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
