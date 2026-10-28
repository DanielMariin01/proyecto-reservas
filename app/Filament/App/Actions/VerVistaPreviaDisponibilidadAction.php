<?php

declare(strict_types=1);

namespace App\Filament\App\Actions;

use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioDisponibilidad;
use App\Support\Hora;
use Carbon\CarbonImmutable;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

/**
 * RF-DIS-05: al guardar un horario, el dueño o el profesional deben poder
 * ver las franjas que vería un cliente para un servicio de ejemplo, sin
 * tener que abrir la página pública. Reutilizable desde cualquier pantalla
 * que tenga un Profesional en contexto (vista del dueño o autoservicio).
 */
final class VerVistaPreviaDisponibilidadAction
{
    public static function make(Closure $profesional): Action
    {
        return Action::make('verVistaPreviaDisponibilidad')
            ->label('Vista previa de disponibilidad')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->modalHeading('Lo que vería un cliente')
            ->modalDescription('Para un servicio de ejemplo y una fecha, estas son las franjas que RF-DIS-05 muestra para evitar confusiones al guardar el horario.')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->schema([
                Select::make('servicio_id')
                    ->label('Servicio de ejemplo')
                    ->options(fn (): array => Servicio::query()
                        ->where('negocio_id', Negocio::actual()->id)
                        ->pluck('nombre', 'id')
                        ->all())
                    ->live()
                    ->required(),
                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->default(fn (): string => CarbonImmutable::now(Negocio::actual()->zona_horaria)->addDay()->toDateString())
                    ->live()
                    ->required(),
                Placeholder::make('franjas')
                    ->label('Franjas disponibles')
                    ->content(fn (Get $get): string => self::calcularFranjas($get, $profesional)),
            ]);
    }

    private static function calcularFranjas(Get $get, Closure $profesional): string
    {
        $servicioId = $get('servicio_id');
        $fechaTexto = $get('fecha');

        if (blank($servicioId) || blank($fechaTexto)) {
            return 'Elige un servicio y una fecha.';
        }

        $servicio = Servicio::find($servicioId);

        if (! $servicio instanceof Servicio) {
            return 'Elige un servicio y una fecha.';
        }

        $negocio = Negocio::actual();
        $fecha = CarbonImmutable::parse($fechaTexto, $negocio->zona_horaria);

        /** @var Profesional $elProfesional */
        $elProfesional = $profesional();

        $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($elProfesional, $servicio, $fecha);

        if ($horas->isEmpty()) {
            return 'No hay horas disponibles ese día.';
        }

        return $horas
            ->map(fn ($hora): string => Hora::formatear($hora->inicio, $negocio->zona_horaria))
            ->implode(' · ');
    }
}
