<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Actions\CrearBloqueoRapido;
use App\DatosTransferencia\DatosBloqueoRapido;
use App\Filament\App\Actions\VerVistaPreviaDisponibilidadAction;
use App\Models\Bloqueo;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * RF-DIS-03: bloqueos rápidos desde el celular del profesional
 * ("no atiendo hoy de 3 a 5"), más su horario y sus bloqueos a la vista.
 *
 * @property-read Schema $form
 */
final class MiHorario extends Page
{
    protected static ?string $slug = 'mi-horario';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Mi horario';

    protected string $view = 'filament.app.pages.mi-horario';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return self::profesionalActual() instanceof Profesional;
    }

    public function mount(): void
    {
        $this->reiniciarFormulario();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('fecha')->label('Fecha')->required(),
                TimePicker::make('hora_inicio')->label('Desde')->seconds(false)->required(),
                TimePicker::make('hora_fin')->label('Hasta')->seconds(false)->after('hora_inicio')->required(),
                TextInput::make('motivo')->label('Motivo (opcional)')->maxLength(255),
            ])
            ->statePath('data')
            ->columns(2);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            VerVistaPreviaDisponibilidadAction::make(fn (): Profesional => self::profesionalActualOFallar()),
        ];
    }

    public function crearBloqueo(): void
    {
        $datos = $this->form->getState();

        app(CrearBloqueoRapido::class)->ejecutar(new DatosBloqueoRapido(
            profesional: self::profesionalActualOFallar(),
            fecha: CarbonImmutable::parse($datos['fecha']),
            horaInicio: $datos['hora_inicio'],
            horaFin: $datos['hora_fin'],
            motivo: $datos['motivo'] ?? null,
        ));

        Notification::make()->title('Bloqueo creado')->success()->send();

        $this->reiniciarFormulario();
    }

    public function eliminarBloqueo(int $bloqueoId): void
    {
        self::profesionalActualOFallar()->bloqueos()->whereKey($bloqueoId)->delete();
    }

    /**
     * @return Collection<int, HorarioTrabajo>
     */
    public function horariosTrabajo(): Collection
    {
        return self::profesionalActualOFallar()
            ->horariosTrabajo()
            ->orderBy('dia_semana')
            ->get();
    }

    /**
     * @return Collection<int, Bloqueo>
     */
    public function bloqueosProximos(): Collection
    {
        return self::profesionalActualOFallar()
            ->bloqueos()
            ->where('fin', '>=', CarbonImmutable::now())
            ->orderBy('inicio')
            ->get();
    }

    private function reiniciarFormulario(): void
    {
        $this->form->fill([
            'fecha' => CarbonImmutable::now(Negocio::actual()->zona_horaria)->toDateString(),
            'hora_inicio' => null,
            'hora_fin' => null,
            'motivo' => null,
        ]);
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
