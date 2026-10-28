<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Enums\Permiso;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * RF-RES-07 (vista de día): columnas por profesional con el color de cada
 * uno en el borde izquierdo de la cita (skill diseno-reservas, sección 7).
 * Vista de semana y arrastrar para mover citas quedan para una iteración
 * siguiente, según lo acordado para T5.
 */
final class AgendaDelDia extends Page
{
    protected static ?string $slug = 'agenda';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Reservas';

    protected static ?string $navigationLabel = 'Agenda';

    protected string $view = 'filament.app.pages.agenda-del-dia';

    public string $fecha = '';

    public static function canAccess(): bool
    {
        $usuario = Filament::auth()->user();

        return $usuario !== null && ($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value));
    }

    public function mount(): void
    {
        $this->fecha = CarbonImmutable::now(Negocio::actual()->zona_horaria)->toDateString();
    }

    public function diaAnterior(): void
    {
        $this->fecha = CarbonImmutable::parse($this->fecha)->subDay()->toDateString();
    }

    public function diaSiguiente(): void
    {
        $this->fecha = CarbonImmutable::parse($this->fecha)->addDay()->toDateString();
    }

    public function irAHoy(): void
    {
        $this->fecha = CarbonImmutable::now(Negocio::actual()->zona_horaria)->toDateString();
    }

    /**
     * @return Collection<int, array{profesional: Profesional, citas: \Illuminate\Database\Eloquent\Collection<int, Cita>}>
     */
    public function columnas(): Collection
    {
        $negocio = Negocio::actual();
        $fechaLocal = CarbonImmutable::parse($this->fecha, $negocio->zona_horaria)->startOfDay();
        $inicioDiaUtc = $fechaLocal->utc();
        $finDiaUtc = $fechaLocal->endOfDay()->utc();

        return Profesional::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Profesional $profesional): array => [
                'profesional' => $profesional,
                'citas' => Cita::query()
                    ->where('profesional_id', $profesional->id)
                    ->where('inicio', '>=', $inicioDiaUtc)
                    ->where('inicio', '<', $finDiaUtc)
                    ->orderBy('inicio')
                    ->get(),
            ]);
    }
}
