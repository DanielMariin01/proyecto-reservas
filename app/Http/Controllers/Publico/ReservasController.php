<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Actions\CrearReserva;
use App\DatosTransferencia\DatosReserva;
use App\Disponibilidad\HoraDisponible;
use App\Enums\OrigenCita;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\HoraYaTomadaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publico\SolicitudReservaPublica;
use App\Http\Resources\Publico\RecursoCitaPublica;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioDisponibilidad;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

final class ReservasController extends Controller
{
    public function __construct(
        private readonly CrearReserva $crearReserva,
        private readonly ServicioDisponibilidad $servicioDisponibilidad,
    ) {}

    public function store(SolicitudReservaPublica $request, Negocio $negocio): JsonResponse
    {
        $servicio = Servicio::query()
            ->where('negocio_id', $negocio->id)
            ->where('visible', true)
            ->findOrFail($request->integer('servicio_id'));

        $profesional = $this->resolverProfesional($request, $negocio);
        $inicio = CarbonImmutable::parse((string) $request->string('inicio'))->utc();

        try {
            $cita = $this->crearReserva->ejecutar(new DatosReserva(
                negocio: $negocio,
                servicio: $servicio,
                nombreCliente: (string) $request->string('nombre'),
                celularCliente: (string) $request->string('celular'),
                correoCliente: $request->filled('correo') ? (string) $request->string('correo') : null,
                inicio: $inicio,
                origen: OrigenCita::Web,
                profesional: $profesional,
                notas: $request->filled('nota') ? (string) $request->string('nota') : null,
            ));
        } catch (HoraYaTomadaException|HoraInvalidaException $excepcion) {
            return response()->json([
                'mensaje' => $excepcion->getMessage(),
                'horasCercanas' => $this->horasCercanas($negocio, $servicio, $profesional, $inicio)
                    ->map(fn (HoraDisponible $hora): string => $hora->inicio->toIso8601String())
                    ->values(),
            ], 409);
        }

        return (new RecursoCitaPublica($cita))->response()->setStatusCode(201);
    }

    private function resolverProfesional(SolicitudReservaPublica $request, Negocio $negocio): ?Profesional
    {
        if (! $request->filled('profesional_id')) {
            return null;
        }

        return Profesional::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->findOrFail($request->integer('profesional_id'));
    }

    /**
     * CU-01, alterno: "la franja se ocupa mientras confirma → mensaje y
     * tres horas cercanas".
     *
     * @return Collection<int, HoraDisponible>
     */
    private function horasCercanas(Negocio $negocio, Servicio $servicio, ?Profesional $profesional, CarbonImmutable $inicio): Collection
    {
        $fechaLocal = CarbonImmutable::parse(
            $inicio->setTimezone($negocio->zona_horaria)->toDateString(),
            $negocio->zona_horaria,
        );

        $profesionales = $profesional !== null
            ? collect([$profesional])
            : $servicio->profesionales()->where('activo', true)->get();

        return $this->servicioDisponibilidad
            ->obtenerFranjasParaCualquierProfesional($profesionales, $servicio, $fechaLocal)
            ->sortBy(fn (HoraDisponible $hora): int => (int) abs($hora->inicio->diffInSeconds($inicio)))
            ->take(3);
    }
}
