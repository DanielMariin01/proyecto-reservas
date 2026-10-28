<?php

declare(strict_types=1);

namespace App\Services;

use App\Disponibilidad\BloqueLibre;
use App\Disponibilidad\CadenaReglasFranja;
use App\Disponibilidad\FabricaEstrategiaFranjas;
use App\Disponibilidad\Franja;
use App\Disponibilidad\HoraDisponible;
use App\Disponibilidad\IntervalosOcupadosPorCitas;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class ServicioDisponibilidad
{
    public function __construct(
        private readonly FabricaEstrategiaFranjas $fabricaEstrategiaFranjas,
        private readonly CadenaReglasFranja $reglasFranja,
        private readonly IntervalosOcupadosPorCitas $intervalosOcupadosPorCitas,
    ) {}

    /**
     * @return Collection<int, HoraDisponible>
     */
    public function obtenerFranjasDisponibles(Profesional $profesional, Servicio $servicio, CarbonImmutable $fecha): Collection
    {
        $bloquesLibres = $profesional->obtenerBloquesLibres($fecha);

        foreach ($this->intervalosOcupadosPorCitas->paraProfesionalYFecha($profesional, $fecha) as $intervalo) {
            $bloquesLibres = BloqueLibre::restar($bloquesLibres, $intervalo->inicio, $intervalo->fin);
        }

        if ($bloquesLibres->isEmpty()) {
            return collect();
        }

        $estrategia = $this->fabricaEstrategiaFranjas->paraServicio($servicio);
        $ahora = CarbonImmutable::now();

        return $estrategia
            ->generar($bloquesLibres, $servicio, $profesional)
            ->filter(fn (Franja $franja): bool => $this->reglasFranja->permite($franja, $servicio, $ahora))
            ->map(fn (Franja $franja): HoraDisponible => HoraDisponible::desdeFranja($franja, $servicio))
            ->values();
    }

    /**
     * Sección 5, paso 8: con "cualquier profesional", unir las franjas de
     * todos los que prestan el servicio. Asignar al que tenga menos citas
     * al confirmar es responsabilidad de ServicioReservas (T5).
     *
     * @param  Collection<int, Profesional>  $profesionales
     * @return Collection<int, HoraDisponible>
     */
    public function obtenerFranjasParaCualquierProfesional(Collection $profesionales, Servicio $servicio, CarbonImmutable $fecha): Collection
    {
        return $profesionales
            ->flatMap(fn (Profesional $profesional) => $this->obtenerFranjasDisponibles($profesional, $servicio, $fecha))
            ->sortBy(fn (HoraDisponible $hora): int => $hora->inicio->getTimestamp())
            ->values();
    }
}
