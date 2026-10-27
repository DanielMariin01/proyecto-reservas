<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagosSuscripcionRelationManager extends RelationManager
{
    protected static string $relationship = 'pagosSuscripcion';

    protected static ?string $title = 'Pagos de suscripción';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fecha')
            ->columns([
                TextColumn::make('fecha')->date('d/m/Y'),
                TextColumn::make('valor')->money('COP', divideBy: 1),
                TextColumn::make('metodo'),
                TextColumn::make('periodo_desde')->date('d/m/Y'),
                TextColumn::make('periodo_hasta')->date('d/m/Y'),
                TextColumn::make('registradoPor.nombre')->label('Registrado por'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
