<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Actions\CambiarEstadoCita;
use App\Actions\RegistrarPagoDeCita;
use App\Enums\EstadoCitaValor;
use App\Enums\MetodoPago;
use App\Exceptions\CitaNoCompletadaException;
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

    public ?int $citaPendientePagoId = null;

    public ?string $metodoPago = null;

    public ?int $valorPago = null;

    public int $propinaPago = 0;

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

    /**
     * CU-08/F3.3: completar no basta, hay que registrar el pago. En vez de
     * cambiar el estado de una vez, abre el formulario inline que pide
     * método, valor (precargado con el precio) y propina antes de confirmar.
     */
    public function marcarCompletada(int $citaId): void
    {
        $profesional = self::profesionalActualOFallar();
        $cita = Cita::query()->where('profesional_id', $profesional->id)->findOrFail($citaId);

        if (! $cita->obtenerEstado()->puedeTransicionarA(EstadoCitaValor::Completada)) {
            Notification::make()->danger()->title('Esta cita no se puede completar')->send();

            return;
        }

        $this->citaPendientePagoId = $citaId;
        $this->metodoPago = null;
        $this->valorPago = $cita->precio;
        $this->propinaPago = 0;
    }

    public function confirmarPago(): void
    {
        abort_if($this->citaPendientePagoId === null, 404);

        $profesional = self::profesionalActualOFallar();
        $cita = Cita::query()->where('profesional_id', $profesional->id)->findOrFail($this->citaPendientePagoId);

        if ($this->metodoPago === null || $this->valorPago === null) {
            Notification::make()->danger()->title('Elige el método de pago y el valor')->send();

            return;
        }

        try {
            $usuario = Filament::auth()->user();
            app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::Completada, $usuario);
            app(RegistrarPagoDeCita::class)->ejecutar(
                cita: $cita,
                metodo: MetodoPago::from($this->metodoPago),
                valor: $this->valorPago,
                propina: $this->propinaPago,
                registradoPor: $usuario,
            );
            Notification::make()->success()->title('Cita completada y pago registrado')->send();
            $this->cancelarPago();
        } catch (TransicionDeEstadoInvalidaException|CitaNoCompletadaException $excepcion) {
            Notification::make()->danger()->title($excepcion->getMessage())->send();
        }
    }

    public function cancelarPago(): void
    {
        $this->citaPendientePagoId = null;
        $this->metodoPago = null;
        $this->valorPago = null;
        $this->propinaPago = 0;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function metodosDePago(): array
    {
        return array_map(
            fn (MetodoPago $metodo): array => ['value' => $metodo->value, 'label' => $metodo->etiqueta()],
            MetodoPago::cases(),
        );
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
