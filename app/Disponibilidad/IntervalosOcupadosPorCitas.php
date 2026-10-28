<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Enums\EstadoCitaValor;
use App\Estados\Cita\FabricaEstadoCita;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Sección 5, paso 3: "restar las citas existentes (pendientes, confirmadas
 * y reprogramadas), cada una ampliada con sus márgenes". Se implementa como
 * los bloqueos de T4 (intervalos ocupados que se restan de los bloques
 * libres), en una clase separada para que Profesional (T4) no tenga que
 * conocer el modelo Cita.
 */
final class IntervalosOcupadosPorCitas
{
    public function __construct(
        private readonly FabricaEstadoCita $fabricaEstadoCita,
    ) {}

    /**
     * @return Collection<int, BloqueLibre>
     */
    public function paraProfesionalYFecha(Profesional $profesional, CarbonImmutable $fecha): Collection
    {
        $negocio = Negocio::findOrFail($profesional->negocio_id);
        $fechaLocal = CarbonImmutable::parse($fecha->format('Y-m-d'), $negocio->zona_horaria)->startOfDay();
        $inicioDiaUtc = $fechaLocal->startOfDay()->utc();
        $finDiaUtc = $fechaLocal->endOfDay()->utc();

        $estadosActivos = array_map(
            fn (EstadoCitaValor $estado): string => $estado->value,
            $this->fabricaEstadoCita->valoresActivos(),
        );

        return Cita::query()
            ->where('profesional_id', $profesional->id)
            ->whereIn('estado', $estadosActivos)
            ->where('inicio', '<', $finDiaUtc)
            ->where('fin', '>', $inicioDiaUtc)
            ->get()
            ->map(function (Cita $cita): BloqueLibre {
                $servicio = Servicio::findOrFail($cita->servicio_id);

                return new BloqueLibre(
                    $cita->inicio->subMinutes($servicio->margenAntesMinutos()),
                    $cita->fin->addMinutes($servicio->margenDespuesMinutos()),
                );
            })
            ->values();
    }
}
