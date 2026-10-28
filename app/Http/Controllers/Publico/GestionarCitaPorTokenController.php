<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Actions\CancelarCita;
use App\Actions\ReprogramarCita;
use App\DatosTransferencia\DatosReprogramacion;
use App\Exceptions\FueraDePoliticaDeCancelacionException;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\HoraYaTomadaException;
use App\Exceptions\MaximoDeReprogramacionesException;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publico\SolicitudGestionCitaPorToken;
use App\Http\Resources\Publico\RecursoCitaPorToken;
use App\Models\Cita;
use App\Models\Negocio;
use App\Services\ServicioEnlaceCita;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

final class GestionarCitaPorTokenController extends Controller
{
    public function __construct(
        private readonly ServicioEnlaceCita $servicioEnlaceCita,
        private readonly CancelarCita $cancelarCita,
        private readonly ReprogramarCita $reprogramarCita,
    ) {}

    public function mostrar(Negocio $negocio, string $token): JsonResponse
    {
        $cita = $this->servicioEnlaceCita->buscarPorToken($negocio, $token);

        return (new RecursoCitaPorToken($cita, $this->servicioEnlaceCita))->response();
    }

    public function gestionar(SolicitudGestionCitaPorToken $request, Negocio $negocio, string $token): JsonResponse
    {
        $cita = $this->servicioEnlaceCita->buscarPorToken($negocio, $token);

        try {
            $cita = $request->string('accion')->value() === 'cancelar'
                ? $this->cancelar($cita, $request)
                : $this->reprogramar($cita, $request);
        } catch (FueraDePoliticaDeCancelacionException|MaximoDeReprogramacionesException $excepcion) {
            return response()->json([
                'mensaje' => $excepcion->getMessage(),
                'mostrarWhatsapp' => true,
            ], 422);
        } catch (HoraInvalidaException|HoraYaTomadaException|TransicionDeEstadoInvalidaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return (new RecursoCitaPorToken($cita, $this->servicioEnlaceCita))->response();
    }

    private function cancelar(Cita $cita, SolicitudGestionCitaPorToken $request): Cita
    {
        $motivo = $request->filled('motivo') ? (string) $request->string('motivo') : 'Cancelada por el cliente desde el enlace del correo';

        return $this->cancelarCita->ejecutar($cita, $motivo);
    }

    private function reprogramar(Cita $cita, SolicitudGestionCitaPorToken $request): Cita
    {
        $nuevoInicio = CarbonImmutable::parse((string) $request->string('inicio'))->utc();

        return $this->reprogramarCita->ejecutar(new DatosReprogramacion(
            cita: $cita,
            nuevoInicio: $nuevoInicio,
        ));
    }
}
