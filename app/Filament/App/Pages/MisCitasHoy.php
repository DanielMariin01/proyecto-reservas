<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Actions\CambiarEstadoCita;
use App\Enums\EstadoCitaValor;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * CU-04: el profesional ve solo sus citas de hoy, ordenadas, y cambia de
 * estado (en atención, completada, no asistió) sin menús ocultos.
 */
final class MisCitasHoy extends Page
{
    protected static ?string $slug = 'mis-citas-hoy';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Mis citas de hoy';

    protected string $view = 'filament.app.pages.mis-citas-hoy';

    public static function canAccess(): bool
    {
        return self::profesionalActual() instanceof Profesional;
    }

    /**
     * @return Collection<int, Cita>
     */
    public function citasDeHoy(): Collection
    {
        $profesional = self::profesionalActualOFallar();
        $negocio = Negocio::findOrFail($profesional->negocio_id);
        $fechaLocal = CarbonImmutable::now($negocio->zona_horaria)->startOfDay();

        return Cita::query()
            ->where('profesional_id', $profesional->id)
            ->where('inicio', '>=', $fechaLocal->utc())
            ->where('inicio', '<', $fechaLocal->endOfDay()->utc())
            ->orderBy('inicio')
            ->get();
    }

    public function marcarEnAtencion(int $citaId): void
    {
        $this->cambiarEstado($citaId, EstadoCitaValor::EnAtencion);
    }

    public function marcarCompletada(int $citaId): void
    {
        $this->cambiarEstado($citaId, EstadoCitaValor::Completada);
    }

    public function marcarNoAsistio(int $citaId): void
    {
        $this->cambiarEstado($citaId, EstadoCitaValor::NoAsistio);
    }

    private function cambiarEstado(int $citaId, EstadoCitaValor $destino): void
    {
        $profesional = self::profesionalActualOFallar();
        $cita = Cita::query()->where('profesional_id', $profesional->id)->findOrFail($citaId);

        try {
            app(CambiarEstadoCita::class)->ejecutar($cita, $destino, Filament::auth()->user());
            Notification::make()->success()->title('Cita actualizada')->send();
        } catch (TransicionDeEstadoInvalidaException $excepcion) {
            Notification::make()->danger()->title($excepcion->getMessage())->send();
        }
    }

    private static function profesionalActual(): ?Profesional
    {
        /** @var Usuario|null $usuario */
        $usuario = Filament::auth()->user();

        return $usuario?->profesional;
    }

    private static function profesionalActualOFallar(): Profesional
    {
        $profesional = self::profesionalActual();

        abort_if($profesional === null, 404);

        return $profesional;
    }
}
