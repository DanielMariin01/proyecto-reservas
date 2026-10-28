<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosReserva;
use App\Disponibilidad\HoraDisponible;
use App\Enums\EstadoCitaValor;
use App\Estados\Cita\FabricaEstadoCita;
use App\Events\CitaCreada;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\HoraYaTomadaException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Profesional;
use App\Services\ServicioDisponibilidad;
use App\Services\ServicioReservas;
use Carbon\CarbonImmutable;

final class CrearReserva
{
    public function __construct(
        private readonly ServicioReservas $servicioReservas,
        private readonly ServicioDisponibilidad $servicioDisponibilidad,
        private readonly FabricaEstadoCita $fabricaEstadoCita,
    ) {}

    public function ejecutar(DatosReserva $datos): Cita
    {
        $cliente = $this->resolverCliente($datos);
        $profesional = $datos->profesional;

        if ($profesional === null) {
            $profesional = $this->elegirProfesional($datos);
        }

        if (! $datos->forzar && ! $this->esFranjaValida($profesional, $datos)) {
            throw new HoraInvalidaException;
        }

        $cita = $this->servicioReservas->reservar(
            profesional: $profesional,
            servicio: $datos->servicio,
            cliente: $cliente,
            inicio: $datos->inicio,
            origen: $datos->origen,
            notas: $datos->notas,
            forzar: $datos->forzar,
        );

        CitaCreada::dispatch($cita);

        return $cita;
    }

    private function resolverCliente(DatosReserva $datos): Cliente
    {
        return Cliente::query()
            ->where('negocio_id', $datos->negocio->id)
            ->where('celular', $datos->celularCliente)
            ->first() ?? Cliente::create([
                'negocio_id' => $datos->negocio->id,
                'nombre' => $datos->nombreCliente,
                'celular' => $datos->celularCliente,
                'correo' => $datos->correoCliente,
            ]);
    }

    /**
     * Sección 5, paso 8: entre los profesionales que prestan el servicio y
     * tienen la franja pedida libre, asigna al que tenga menos citas ese
     * día.
     */
    private function elegirProfesional(DatosReserva $datos): Profesional
    {
        $candidatos = $datos->servicio->profesionales()->where('activo', true)->get();

        $disponibles = $candidatos->filter(
            fn (Profesional $candidato): bool => $this->esFranjaValida($candidato, $datos),
        );

        $elegido = $disponibles->sortBy(
            fn (Profesional $candidato): int => $this->citasActivasEseDia($candidato, $datos),
        )->first();

        if ($elegido === null) {
            throw new HoraYaTomadaException;
        }

        return $elegido;
    }

    private function esFranjaValida(Profesional $profesional, DatosReserva $datos): bool
    {
        $fechaLocal = CarbonImmutable::parse(
            $datos->inicio->setTimezone($datos->negocio->zona_horaria)->toDateString(),
            $datos->negocio->zona_horaria,
        );

        return $this->servicioDisponibilidad
            ->obtenerFranjasDisponibles($profesional, $datos->servicio, $fechaLocal)
            ->contains(fn (HoraDisponible $hora): bool => $hora->inicio->equalTo($datos->inicio));
    }

    private function citasActivasEseDia(Profesional $profesional, DatosReserva $datos): int
    {
        $fechaLocal = $datos->inicio->setTimezone($datos->negocio->zona_horaria)->startOfDay();
        $inicioDiaUtc = $fechaLocal->utc();
        $finDiaUtc = $fechaLocal->endOfDay()->utc();

        $estadosActivos = array_map(
            fn (EstadoCitaValor $estado): string => $estado->value,
            $this->fabricaEstadoCita->valoresActivos(),
        );

        return Cita::query()
            ->where('profesional_id', $profesional->id)
            ->whereIn('estado', $estadosActivos)
            ->where('inicio', '>=', $inicioDiaUtc)
            ->where('inicio', '<', $finDiaUtc)
            ->count();
    }
}
