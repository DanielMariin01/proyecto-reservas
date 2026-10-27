<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\RelationManagers;

use App\Models\Negocio;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiciosRelationManager extends RelationManager
{
    protected static string $relationship = 'servicios';

    protected static ?string $title = 'Servicios que presta';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre'),
                TextColumn::make('pivot.precio')->label('Precio propio')->default('Usa el del servicio'),
                TextColumn::make('pivot.duracion_minutos')->label('Duración propia (min)')->default('Usa la del servicio'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelectOptionsQuery(fn ($query) => $query->where('negocio_id', Negocio::actual()->id))
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('precio')->numeric()->prefix('$'),
                        TextInput::make('duracion_minutos')->label('Duración (min)')->numeric(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                        TextInput::make('precio')->numeric()->prefix('$'),
                        TextInput::make('duracion_minutos')->label('Duración (min)')->numeric(),
                    ])
                    ->fillForm(fn ($record): array => [
                        'precio' => $record->pivot->precio,
                        'duracion_minutos' => $record->pivot->duracion_minutos,
                    ])
                    ->action(function ($record, array $data): void {
                        $record->pivot->update($data);
                    }),
                DetachAction::make(),
            ]);
    }
}
