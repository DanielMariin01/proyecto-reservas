<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoCitaValor;
use App\Enums\OrigenCita;
use App\Estados\Cita\FabricaEstadoCita;
use App\Exceptions\HoraYaTomadaException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Mecanismo de concurrencia (sección 5, "bloqueo optimista al confirmar";
 * sección 10, "validación en transacción con bloqueo de fila del
 * profesional"): dentro de una transacción se bloquea la fila del
 * profesional con SELECT ... FOR UPDATE, lo que serializa cualquier otra
 * transacción que intente lo mismo para ese profesional hasta que esta
 * termine. Con la fila ya bloqueada se repite la verificación de choque;
 * si al despertar la segunda transacción ya existe una cita en esa franja,
 * falla y nunca llega a crear una fila duplicada. El candado es sobre la
 * fila del profesional, no sobre un rango de tiempo, por eso funciona sin
 * restricción de exclusión (MariaDB no la tiene).
 */
final class ServicioReservas
{
    public function __construct(
        private readonly FabricaEstadoCita $fabricaEstadoCita,
    ) {}

    public function reservar(
        Profesional $profesional,
        Servicio $servicio,
        Cliente $cliente,
        CarbonImmutable $inicio,
        OrigenCita $origen,
        ?string $notas = null,
        bool $forzar = false,
    ): Cita {
        $fin = $inicio->addMinutes($servicio->duracion_minutos);

        return DB::transaction(function () use ($profesional, $servicio, $cliente, $inicio, $fin, $origen, $notas, $forzar): Cita {
            Profesional::query()->whereKey($profesional->id)->lockForUpdate()->first();

            if (! $forzar && $this->estaOcupado($profesional, $inicio, $fin)) {
                throw new HoraYaTomadaException;
            }

            $negocio = Negocio::findOrFail($profesional->negocio_id);

            return Cita::create([
                'negocio_id' => $profesional->negocio_id,
                'profesional_id' => $profesional->id,
                'servicio_id' => $servicio->id,
                'cliente_id' => $cliente->id,
                'inicio' => $inicio,
                'fin' => $fin,
                'estado' => $negocio->confirmacion_manual ? EstadoCitaValor::Pendiente : EstadoCitaValor::Confirmada,
                'precio' => $profesional->precioParaServicio($servicio),
                'origen' => $origen,
                'notas' => $notas,
                'token_cancelacion' => Str::random(40),
            ]);
        });
    }

    /**
     * Misma garantía de concurrencia que reservar(), pero sobre una cita
     * que ya existe: se usa desde ReprogramarCita. La propia cita se
     * excluye de la verificación de choque porque va a dejar su franja
     * vieja para ocupar la nueva.
     */
    public function moverA(Cita $cita, CarbonImmutable $nuevoInicio, int $duracionMinutos, bool $forzar = false): Cita
    {
        $nuevoFin = $nuevoInicio->addMinutes($duracionMinutos);

        return DB::transaction(function () use ($cita, $nuevoInicio, $nuevoFin, $forzar): Cita {
            Profesional::query()->whereKey($cita->profesional_id)->lockForUpdate()->first();

            $profesional = Profesional::findOrFail($cita->profesional_id);

            if (! $forzar && $this->estaOcupado($profesional, $nuevoInicio, $nuevoFin, excluirCitaId: $cita->id)) {
                throw new HoraYaTomadaException;
            }

            $cita->update([
                'inicio' => $nuevoInicio,
                'fin' => $nuevoFin,
            ]);

            return $cita;
        });
    }

    private function estaOcupado(Profesional $profesional, CarbonImmutable $inicio, CarbonImmutable $fin, ?int $excluirCitaId = null): bool
    {
        $estadosActivos = array_map(
            fn (EstadoCitaValor $estado): string => $estado->value,
            $this->fabricaEstadoCita->valoresActivos(),
        );

        return Cita::query()
            ->where('profesional_id', $profesional->id)
            ->whereIn('estado', $estadosActivos)
            ->where('inicio', '<', $fin)
            ->where('fin', '>', $inicio)
            ->when($excluirCitaId !== null, fn ($query) => $query->whereKeyNot($excluirCitaId))
            ->exists();
    }
}
