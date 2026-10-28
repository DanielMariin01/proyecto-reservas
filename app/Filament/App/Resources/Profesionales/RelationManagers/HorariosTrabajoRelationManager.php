<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\RelationManagers;

use App\Enums\DiaSemana;
use App\Models\Negocio;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Component;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HorariosTrabajoRelationManager extends RelationManager
{
    protected static string $relationship = 'horariosTrabajo';

    protected static ?string $title = 'Horario semanal';

    /**
     * @return array<Component>
     */
    private static function campos(): array
    {
        return [
            Select::make('dia_semana')
                ->label('Día')
                ->options(DiaSemana::class)
                ->required(),
            TimePicker::make('hora_inicio')
                ->label('Desde')
                ->seconds(false)
                ->required(),
            TimePicker::make('hora_fin')
                ->label('Hasta')
                ->seconds(false)
                ->after('hora_inicio')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dia_semana')
            ->columns([
                TextColumn::make('dia_semana'),
                TextColumn::make('hora_inicio')->label('Desde')->time('H:i'),
                TextColumn::make('hora_fin')->label('Hasta')->time('H:i'),
            ])
            ->defaultSort('dia_semana')
            ->headerActions([
                CreateAction::make()
                    ->schema(self::campos())
                    ->mutateDataUsing(fn (array $data): array => [...$data, 'negocio_id' => Negocio::actual()->id]),
            ])
            ->recordActions([
                EditAction::make()->schema(self::campos()),
                DeleteAction::make(),
            ]);
    }
}
