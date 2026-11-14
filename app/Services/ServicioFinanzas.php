<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\GranularidadTendencia;
use App\Enums\RangoFinanzas;
use App\Models\Gasto;
use App\Models\Negocio;
use App\Models\Pago;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * RF-REP-02 (adelantado desde Fase 2): ingresos por periodo y por método
 * de pago, gastos y el neto resultante. Mismo criterio de "caja" que
 * ServicioPanelDelDia::ingresosRegistrados — se mide por la fecha en que
 * el pago se registró (created_at), no por la fecha de la cita.
 */
final class ServicioFinanzas
{
    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function rangoLocal(RangoFinanzas $rango, Negocio $negocio): array
    {
        $hoyLocal = CarbonImmutable::now($negocio->zona_horaria)->startOfDay();

        return match ($rango) {
            RangoFinanzas::Mes => [$hoyLocal->startOfMonth(), $hoyLocal->startOfMonth()->addMonth()],
            RangoFinanzas::QuinceDias => [$hoyLocal->subDays(14), $hoyLocal->addDay()],
            RangoFinanzas::Anio => [$hoyLocal->startOfYear(), $hoyLocal->startOfYear()->addYear()],
        };
    }

    /**
     * @return Collection<int, Pago>
     */
    public function pagosDelRango(Negocio $negocio, CarbonImmutable $inicioLocal, CarbonImmutable $finLocal): Collection
    {
        return Pago::query()
            ->where('negocio_id', $negocio->id)
            ->whereNull('anulado_en')
            ->where('created_at', '>=', $inicioLocal->utc())
            ->where('created_at', '<', $finLocal->utc())
            ->get();
    }

    /**
     * @param  Collection<int, Pago>  $pagosDelRango
     */
    public function ingresosBrutos(Collection $pagosDelRango): int
    {
        return (int) $pagosDelRango->sum(fn (Pago $pago): int => $pago->valor + $pago->propina);
    }

    /**
     * @param  Collection<int, Pago>  $pagosDelRango
     */
    public function totalPropinas(Collection $pagosDelRango): int
    {
        return (int) $pagosDelRango->sum('propina');
    }

    /**
     * @param  Collection<int, Pago>  $pagosDelRango
     * @return array<string, array{etiqueta: string, total: int}>
     */
    public function ingresosPorMetodo(Collection $pagosDelRango): array
    {
        return $pagosDelRango
            ->groupBy(fn (Pago $pago): string => $pago->metodo->value)
            ->map(function (Collection $pagos): array {
                /** @var Pago $primerPago */
                $primerPago = $pagos->first();

                return [
                    'etiqueta' => $primerPago->metodo->etiqueta(),
                    'total' => (int) $pagos->sum(fn (Pago $pago): int => $pago->valor + $pago->propina),
                ];
            })
            ->all();
    }

    /**
     * @return Collection<int, Gasto>
     */
    public function gastosDelRango(Negocio $negocio, CarbonImmutable $inicioLocal, CarbonImmutable $finLocal): Collection
    {
        return Gasto::query()
            ->where('negocio_id', $negocio->id)
            ->where('fecha', '>=', $inicioLocal->toDateString())
            ->where('fecha', '<', $finLocal->toDateString())
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  Collection<int, Gasto>  $gastosDelRango
     */
    public function totalGastos(Collection $gastosDelRango): int
    {
        return (int) $gastosDelRango->sum('monto');
    }

    /**
     * Serie de ingresos y gastos por punto (día o mes, según la
     * granularidad del rango), para la gráfica de tendencia.
     *
     * @param  Collection<int, Pago>  $pagosDelRango
     * @param  Collection<int, Gasto>  $gastosDelRango
     * @return list<array{etiqueta: string, ingresos: int, gastos: int}>
     */
    public function tendencia(
        RangoFinanzas $rango,
        CarbonImmutable $inicioLocal,
        CarbonImmutable $finLocal,
        Collection $pagosDelRango,
        Collection $gastosDelRango,
        string $zonaHoraria,
    ): array {
        $granularidad = $rango->agrupacionTendencia();

        $ingresosPorClave = $pagosDelRango->groupBy(
            fn (Pago $pago): string => $this->claveDePunto($pago->created_at?->setTimezone($zonaHoraria) ?? $inicioLocal, $granularidad),
        )->map(fn (Collection $pagos): int => (int) $pagos->sum(fn (Pago $pago): int => $pago->valor + $pago->propina));

        $gastosPorClave = $gastosDelRango->groupBy(
            fn (Gasto $gasto): string => $this->claveDePunto($gasto->fecha, $granularidad),
        )->map(fn (Collection $gastos): int => (int) $gastos->sum('monto'));

        $puntos = [];
        $cursor = $inicioLocal;

        while ($cursor->lessThan($finLocal)) {
            $clave = $this->claveDePunto($cursor, $granularidad);

            $puntos[] = [
                'etiqueta' => $granularidad === GranularidadTendencia::Dia ? $cursor->format('d/m') : $cursor->translatedFormat('M'),
                'ingresos' => $ingresosPorClave->get($clave, 0),
                'gastos' => $gastosPorClave->get($clave, 0),
            ];

            $cursor = $granularidad === GranularidadTendencia::Dia ? $cursor->addDay() : $cursor->addMonth();
        }

        return $puntos;
    }

    private function claveDePunto(CarbonInterface $momento, GranularidadTendencia $granularidad): string
    {
        return $granularidad === GranularidadTendencia::Dia ? $momento->toDateString() : $momento->format('Y-m');
    }
}
