<?php

declare(strict_types=1);

namespace App\Services;

use App\Disponibilidad\BloqueLibre;
use App\Disponibilidad\IntervalosOcupadosPorCitas;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Profesional;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * RF-REP-01: citas de hoy, próximas, ingresos registrados y huecos libres.
 * Base del cierre del día (F5): el dueño revisa esto antes de corregir
 * pagos mal registrados y cerrar la caja (eso, y el detalle por
 * profesional/servicio de RF-REP-02, queda para Fase 2).
 */
final class ServicioPanelDelDia
{
    public function __construct(private readonly IntervalosOcupadosPorCitas $intervalosOcupadosPorCitas) {}

    /**
     * @return Collection<int, Cita>
     */
    public function citasDeHoy(Negocio $negocio): Collection
    {
        [$inicioDiaUtc, $finDiaUtc] = $this->rangoDeHoyUtc($negocio);

        return Cita::query()
            ->where('negocio_id', $negocio->id)
            ->where('inicio', '>=', $inicioDiaUtc)
            ->where('inicio', '<', $finDiaUtc)
            ->orderBy('inicio')
            ->get();
    }

    /**
     * Las de hoy que todavía no empiezan: lo que falta por atender.
     *
     * @param  Collection<int, Cita>  $citasDeHoy
     * @return Collection<int, Cita>
     */
    public function citasProximas(Collection $citasDeHoy): Collection
    {
        return $citasDeHoy->filter(
            fn (Cita $cita): bool => $cita->inicio->greaterThan(CarbonImmutable::now()),
        )->values();
    }

    /**
     * Suma de lo efectivamente cobrado hoy (pagos no anulados registrados
     * hoy), como en una caja diaria: lo que entró hoy, sin importar la
     * fecha de la cita.
     */
    public function ingresosRegistrados(Negocio $negocio): int
    {
        [$inicioDiaUtc, $finDiaUtc] = $this->rangoDeHoyUtc($negocio);

        $pagos = Pago::query()
            ->where('negocio_id', $negocio->id)
            ->whereNull('anulado_en')
            ->where('created_at', '>=', $inicioDiaUtc)
            ->where('created_at', '<', $finDiaUtc)
            ->get();

        return (int) $pagos->sum(fn (Pago $pago): int => $pago->valor + $pago->propina);
    }

    /**
     * Huecos libres de hoy por profesional, para promocionar en WhatsApp
     * (F5.3): reusa los mismos bloques de T4 y el mismo resta-citas de T5
     * (IntervalosOcupadosPorCitas), sin recalcular el algoritmo.
     *
     * @return Collection<int, array{profesional: Profesional, bloques: Collection<int, BloqueLibre>}>
     */
    public function huecosLibres(Negocio $negocio): Collection
    {
        $fechaLocal = CarbonImmutable::now($negocio->zona_horaria)->startOfDay();

        return Profesional::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (Profesional $profesional) use ($fechaLocal): array {
                $bloques = $profesional->obtenerBloquesLibres($fechaLocal);

                foreach ($this->intervalosOcupadosPorCitas->paraProfesionalYFecha($profesional, $fechaLocal) as $intervalo) {
                    $bloques = BloqueLibre::restar($bloques, $intervalo->inicio, $intervalo->fin);
                }

                return ['profesional' => $profesional, 'bloques' => $bloques];
            });
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function rangoDeHoyUtc(Negocio $negocio): array
    {
        $fechaLocal = CarbonImmutable::now($negocio->zona_horaria)->startOfDay();

        return [$fechaLocal->utc(), $fechaLocal->endOfDay()->utc()];
    }
}
