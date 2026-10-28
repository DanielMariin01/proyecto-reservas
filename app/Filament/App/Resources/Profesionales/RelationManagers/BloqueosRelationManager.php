<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\RelationManagers;

use App\Enums\TipoBloqueo;
use App\Models\Bloqueo;
use App\Models\Negocio;
use Carbon\CarbonImmutable;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Component;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BloqueosRelationManager extends RelationManager
{
    protected static string $relationship = 'bloqueos';

    protected static ?string $title = 'Bloqueos y aperturas especiales';

    private static function hoyEnElNegocio(): string
    {
        return CarbonImmutable::now(Negocio::actual()->zona_horaria)->toDateString();
    }

    /**
     * @return array<Component>
     */
    private static function campos(): array
    {
        return [
            Select::make('tipo')
                ->options(TipoBloqueo::class)
                ->default(TipoBloqueo::Bloqueo)
                ->required(),
            DatePicker::make('fecha_inicio')->label('Fecha de inicio')->default(self::hoyEnElNegocio())->required(),
            TimePicker::make('hora_inicio')->label('Desde')->seconds(false)->required(),
            DatePicker::make('fecha_fin')->label('Fecha de fin')->default(self::hoyEnElNegocio())->required(),
            TimePicker::make('hora_fin')->label('Hasta')->seconds(false)->required(),
            TextInput::make('motivo')->maxLength(255),
        ];
    }

    /**
     * Las fechas/horas que ve el dueño están en la zona del negocio (RNF-08);
     * aquí se combinan y se convierten a UTC antes de guardar.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function aDatosDeColumna(array $data, Negocio $negocio): array
    {
        $inicio = CarbonImmutable::parse("{$data['fecha_inicio']} {$data['hora_inicio']}", $negocio->zona_horaria)->utc();
        $fin = CarbonImmutable::parse("{$data['fecha_fin']} {$data['hora_fin']}", $negocio->zona_horaria)->utc();

        return [
            'tipo' => $data['tipo'],
            'motivo' => $data['motivo'] ?? null,
            'inicio' => $inicio,
            'fin' => $fin,
            'negocio_id' => $negocio->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function desdeDatosDeColumna(Bloqueo $bloqueo, Negocio $negocio): array
    {
        $inicio = $bloqueo->inicio->setTimezone($negocio->zona_horaria);
        $fin = $bloqueo->fin->setTimezone($negocio->zona_horaria);

        return [
            'tipo' => $bloqueo->tipo,
            'motivo' => $bloqueo->motivo,
            'fecha_inicio' => $inicio->toDateString(),
            'hora_inicio' => $inicio->format('H:i'),
            'fecha_fin' => $fin->toDateString(),
            'hora_fin' => $fin->format('H:i'),
        ];
    }

    public function table(Table $table): Table
    {
        $negocio = Negocio::actual();

        return $table
            ->recordTitleAttribute('motivo')
            ->columns([
                TextColumn::make('tipo'),
                TextColumn::make('inicio')->label('Desde')->dateTime('d/m/Y H:i', $negocio->zona_horaria),
                TextColumn::make('fin')->label('Hasta')->dateTime('d/m/Y H:i', $negocio->zona_horaria),
                TextColumn::make('motivo')->default('—'),
            ])
            ->defaultSort('inicio', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->schema(self::campos())
                    ->mutateDataUsing(fn (array $data): array => self::aDatosDeColumna($data, $negocio)),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema(self::campos())
                    ->fillForm(fn (Bloqueo $record): array => self::desdeDatosDeColumna($record, $negocio))
                    ->mutateDataUsing(fn (array $data): array => self::aDatosDeColumna($data, $negocio)),
                DeleteAction::make(),
            ]);
    }
}
