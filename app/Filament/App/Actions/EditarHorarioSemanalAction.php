<?php

declare(strict_types=1);

namespace App\Filament\App\Actions;

use App\Actions\GuardarHorarioSemanal;
use App\DatosTransferencia\DatosHorarioSemanal;
use App\Enums\DiaSemana;
use App\Exceptions\RangosDeHorarioSuperpuestosException;
use App\Models\Profesional;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * Editor visual del horario semanal (reemplaza la tabla con crear/editar/
 * borrar por fila): un bloque por día con una casilla "disponible" y uno o
 * más rangos de hora editables ahí mismo, todo guardado de una sola vez.
 * Reutilizable en cualquier pantalla que tenga un Profesional en contexto.
 */
final class EditarHorarioSemanalAction
{
    public static function make(Closure $profesional): Action
    {
        return Action::make('editarHorarioSemanal')
            ->label('Editar horario')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->color('gray')
            ->modalHeading('Horario semanal')
            ->modalWidth(Width::ThreeExtraLarge)
            ->fillForm(fn (): array => ['dias' => self::estadoInicial($profesional())])
            ->schema(self::campos())
            ->action(function (array $data) use ($profesional): void {
                try {
                    app(GuardarHorarioSemanal::class)->ejecutar(new DatosHorarioSemanal(
                        profesional: $profesional(),
                        rangosPorDia: self::aRangosPorDia($data['dias'] ?? []),
                    ));
                } catch (RangosDeHorarioSuperpuestosException $excepcion) {
                    Notification::make()->danger()->title($excepcion->getMessage())->send();

                    throw new Halt;
                }

                Notification::make()->success()->title('Horario guardado')->send();
            });
    }

    /**
     * @return array<int, Section>
     */
    private static function campos(): array
    {
        return array_map(
            fn (DiaSemana $dia): Section => self::seccionDelDia($dia),
            DiaSemana::cases(),
        );
    }

    private static function seccionDelDia(DiaSemana $dia): Section
    {
        $prefijo = "dias.{$dia->value}";

        return Section::make($dia->etiqueta())
            ->schema([
                Checkbox::make("{$prefijo}.disponible")
                    ->label('Disponible este día')
                    ->live()
                    ->default(false),
                Repeater::make("{$prefijo}.rangos")
                    ->label('Horas')
                    ->hiddenLabel()
                    ->schema([
                        TimePicker::make('hora_inicio')->label('Desde')->seconds(false)->required(),
                        TimePicker::make('hora_fin')->label('Hasta')->seconds(false)->after('hora_inicio')->required(),
                    ])
                    ->columns(2)
                    ->addActionLabel('Agregar rango')
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->visible(fn (Get $get): bool => (bool) $get("{$prefijo}.disponible")),
            ]);
    }

    /**
     * @return array<int, array{disponible: bool, rangos: list<array{hora_inicio: string, hora_fin: string}>}>
     */
    private static function estadoInicial(Profesional $profesional): array
    {
        $horariosPorDia = $profesional->horariosTrabajo()->orderBy('hora_inicio')->get()->groupBy('dia_semana');

        $estado = [];

        foreach (DiaSemana::cases() as $dia) {
            $horarios = $horariosPorDia->get($dia->value, Collection::make());

            $rangos = [];

            foreach ($horarios as $horario) {
                $rangos[] = [
                    'hora_inicio' => mb_substr((string) $horario->hora_inicio, 0, 5),
                    'hora_fin' => mb_substr((string) $horario->hora_fin, 0, 5),
                ];
            }

            $estado[$dia->value] = [
                'disponible' => $horarios->isNotEmpty(),
                'rangos' => $rangos,
            ];
        }

        return $estado;
    }

    /**
     * @param  array<string, array{disponible?: bool, rangos?: list<array{hora_inicio: string, hora_fin: string}>}>  $dias
     * @return array<int, list<array{hora_inicio: string, hora_fin: string}>>
     */
    private static function aRangosPorDia(array $dias): array
    {
        $rangosPorDia = [];

        foreach ($dias as $diaValor => $datosDelDia) {
            if (! ($datosDelDia['disponible'] ?? false)) {
                continue;
            }

            $rangos = $datosDelDia['rangos'] ?? [];

            if ($rangos === []) {
                continue;
            }

            $rangosPorDia[(int) $diaValor] = $rangos;
        }

        return $rangosPorDia;
    }
}
