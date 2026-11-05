<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Disponibilidad\BloqueLibre;
use App\Disponibilidad\IntervalosOcupadosPorCitas;
use App\Enums\Permiso;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Profesional;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * RF-REP-01: citas de hoy, próximas, ingresos registrados y huecos libres.
 * Base del cierre del día (F5): el dueño revisa esto antes de corregir
 * pagos mal registrados y cerrar la caja (eso, y el detalle por
 * profesional/servicio de RF-REP-02, queda para Fase 2).
 */
final class PanelDelDia extends Page
{
    protected static ?string $slug = 'panel-del-dia';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Reservas';

    protected static ?string $navigationLabel = 'Panel del día';

    protected string $view = 'filament.app.pages.panel-del-dia';

    public static function canAccess(): bool
    {
        $usuario = Filament::auth()->user();

        return $usuario !== null && ($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value));
    }

    /**
     * @return Collection<int, Cita>
     */
    public function citasDeHoy(): Collection
    {
        [$inicioDiaUtc, $finDiaUtc] = $this->rangoDeHoyUtc();

        return Cita::query()
            ->where('inicio', '>=', $inicioDiaUtc)
            ->where('inicio', '<', $finDiaUtc)
            ->orderBy('inicio')
            ->get();
    }

    /**
     * Las de hoy que todavía no empiezan: lo que falta por atender.
     *
     * @return Collection<int, Cita>
     */
    public function citasProximas(): Collection
    {
        return $this->citasDeHoy()->filter(
            fn (Cita $cita): bool => $cita->inicio->greaterThan(CarbonImmutable::now()),
        )->values();
    }

    /**
     * Suma de lo efectivamente cobrado hoy (pagos no anulados registrados
     * hoy), como en una caja diaria: lo que entró hoy, sin importar la
     * fecha de la cita.
     */
    public function ingresosRegistrados(): int
    {
        [$inicioDiaUtc, $finDiaUtc] = $this->rangoDeHoyUtc();

        $pagos = Pago::query()
            ->where('negocio_id', Negocio::actual()->id)
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
    public function huecosLibres(): Collection
    {
        $negocio = Negocio::actual();
        $fechaLocal = CarbonImmutable::now($negocio->zona_horaria)->startOfDay();
        $intervalosOcupados = app(IntervalosOcupadosPorCitas::class);

        return Profesional::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (Profesional $profesional) use ($fechaLocal, $intervalosOcupados): array {
                $bloques = $profesional->obtenerBloquesLibres($fechaLocal);

                foreach ($intervalosOcupados->paraProfesionalYFecha($profesional, $fechaLocal) as $intervalo) {
                    $bloques = BloqueLibre::restar($bloques, $intervalo->inicio, $intervalo->fin);
                }

                return ['profesional' => $profesional, 'bloques' => $bloques];
            });
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function rangoDeHoyUtc(): array
    {
        $fechaLocal = CarbonImmutable::now(Negocio::actual()->zona_horaria)->startOfDay();

        return [$fechaLocal->utc(), $fechaLocal->endOfDay()->utc()];
    }
}
