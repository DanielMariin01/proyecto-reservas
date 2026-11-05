<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Actions\CancelarCita;
use App\Actions\CrearResena;
use App\Actions\ReprogramarCita;
use App\DatosTransferencia\DatosNuevaResena;
use App\DatosTransferencia\DatosReprogramacion;
use App\Exceptions\FueraDePoliticaDeCancelacionException;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\HoraYaTomadaException;
use App\Exceptions\MaximoDeReprogramacionesException;
use App\Exceptions\ResenaNoElegibleException;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publico\SolicitudGestionCitaPorToken;
use App\Http\Resources\Publico\RecursoCitaPorToken;
use App\Models\Cita;
use App\Models\Negocio;
use App\Services\ServicioEnlaceCita;
use App\Services\ServicioResenas;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

final class GestionarCitaPorTokenController extends Controller
{
    public function __construct(
        private readonly ServicioEnlaceCita $servicioEnlaceCita,
        private readonly ServicioResenas $servicioResenas,
        private readonly CancelarCita $cancelarCita,
        private readonly ReprogramarCita $reprogramarCita,
        private readonly CrearResena $crearResena,
    ) {}

    public function mostrar(Negocio $negocio, string $token): JsonResponse
    {
        $cita = $this->servicioEnlaceCita->buscarPorToken($negocio, $token);

        return (new RecursoCitaPorToken($cita, $this->servicioEnlaceCita, $this->servicioResenas))->response();
    }

    public function gestionar(SolicitudGestionCitaPorToken $request, Negocio $negocio, string $token): JsonResponse
    {
        $cita = $this->servicioEnlaceCita->buscarPorToken($negocio, $token);

        try {
            $cita = match ($request->string('accion')->value()) {
                'cancelar' => $this->cancelar($cita, $request),
                'reprogramar' => $this->reprogramar($cita, $request),
                default => $this->dejarResena($cita, $request),
            };
        } catch (FueraDePoliticaDeCancelacionException|MaximoDeReprogramacionesException $excepcion) {
            return response()->json([
                'mensaje' => $excepcion->getMessage(),
                'mostrarWhatsapp' => true,
            ], 422);
        } catch (HoraInvalidaException|HoraYaTomadaException|TransicionDeEstadoInvalidaException|ResenaNoElegibleException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return (new RecursoCitaPorToken($cita, $this->servicioEnlaceCita, $this->servicioResenas))->response();
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

    private function dejarResena(Cita $cita, SolicitudGestionCitaPorToken $request): Cita
    {
        $this->crearResena->ejecutar(new DatosNuevaResena(
            cita: $cita,
            calificacion: $request->integer('calificacion'),
            comentario: (string) $request->string('comentario'),
        ));

        return $cita;
    }
}
