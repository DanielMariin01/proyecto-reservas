<?php

declare(strict_types=1);

namespace App\Services;

use App\Disponibilidad\BloqueLibre;
use App\Disponibilidad\CadenaReglasFranja;
use App\Disponibilidad\FabricaEstrategiaFranjas;
use App\Disponibilidad\Franja;
use App\Disponibilidad\HoraDisponible;
use App\Disponibilidad\IntervalosOcupadosPorCitas;
use App\Models\Negocio;
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

    /**
     * API pública GET /disponibilidad?mes=: qué días del mes tienen al
     * menos una hora libre, para que el calendario deshabilite los demás.
     * Reutiliza el mismo cálculo día por día, sin duplicar el algoritmo;
     * no mira más allá de la ventana máxima del negocio.
     *
     * @return list<string>
     */
    public function obtenerDiasConDisponibilidadEnMes(
        Negocio $negocio,
        Servicio $servicio,
        ?Profesional $profesional,
        string $mesTexto,
    ): array {
        $primerDiaDelMes = CarbonImmutable::parse("{$mesTexto}-01", $negocio->zona_horaria)->startOfDay();
        $hoy = CarbonImmutable::now($negocio->zona_horaria)->startOfDay();
        $limite = $hoy->addDays($negocio->ventana_maxima_dias);

        $profesionales = $profesional !== null
            ? collect([$profesional])
            : $servicio->profesionales()->where('activo', true)->get();

        $dias = [];

        for ($dia = $primerDiaDelMes; $dia->month === $primerDiaDelMes->month; $dia = $dia->addDay()) {
            if ($dia->lessThan($hoy) || $dia->greaterThan($limite)) {
                continue;
            }

            $tieneHoras = $this->obtenerFranjasParaCualquierProfesional($profesionales, $servicio, $dia)->isNotEmpty();

            if ($tieneHoras) {
                $dias[] = $dia->toDateString();
            }
        }

        return $dias;
    }
}
