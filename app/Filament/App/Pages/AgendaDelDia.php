<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Enums\Permiso;
use App\Filament\App\Resources\Citas\AccionesDeCita;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
        $usuario = Auth::guard('web')->user();

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

    /**
     * Las mismas acciones de `CitasTable` (completar con pago, cambiar
     * estado, reprogramar, cancelar, anular pago), vinculadas a esta
     * cita con `->record()` en vez de depender de una tabla — ninguna
     * lógica nueva, solo otra puerta de entrada a la misma.
     */
    public function accionesPara(Cita $cita): ActionGroup
    {
        return ActionGroup::make([
            AccionesDeCita::completarConPago()->record($cita),
            AccionesDeCita::cambiarEstado()->record($cita),
            AccionesDeCita::reprogramar()->record($cita),
            AccionesDeCita::cancelar()->record($cita),
            AccionesDeCita::anularPago()->record($cita),
        ])
            ->label('Acciones')
            ->hiddenLabel()
            ->icon(Heroicon::OutlinedEllipsisVertical)
            ->color('gray')
            ->size('sm');
    }

    /**
     * Esta página no tiene tabla, así que Filament no resuelve la acción
     * montada contra un registro por sí sola: necesita un método del
     * mismo nombre que la acción (de ahí los métodos de abajo). `record()`
     * en `accionesPara()` hace que Filament incluya el id de la cita como
     * `recordKey` en el contexto del botón (`Action::getContext()`); aquí
     * lo leemos de vuelta para reconstruir la cita en el servidor.
     */
    public function getDefaultActionRecord(Action $action): ?Model
    {
        $citaId = $this->mountedActions[$action->getNestingIndex() ?? -1]['context']['recordKey'] ?? null;

        if (! is_int($citaId) && ! is_string($citaId)) {
            return null;
        }

        return Cita::query()->whereKey($citaId)->first();
    }

    public function completarConPagoAction(): Action
    {
        return AccionesDeCita::completarConPago();
    }

    public function cambiarEstadoAction(): Action
    {
        return AccionesDeCita::cambiarEstado();
    }

    public function reprogramarAction(): Action
    {
        return AccionesDeCita::reprogramar();
    }

    public function cancelarAction(): Action
    {
        return AccionesDeCita::cancelar();
    }

    public function anularPagoAction(): Action
    {
        return AccionesDeCita::anularPago();
    }
}
