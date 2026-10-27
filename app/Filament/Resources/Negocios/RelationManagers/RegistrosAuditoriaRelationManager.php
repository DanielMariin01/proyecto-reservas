<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistrosAuditoriaRelationManager extends RelationManager
{
    protected static string $relationship = 'registrosAuditoria';

    protected static ?string $title = 'Últimos cambios';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('accion')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                TextColumn::make('accion'),
                TextColumn::make('usuario.nombre')->label('Usuario')->default('Sistema'),
                IconColumn::make('hecho_por_soporte')->label('Soporte')->boolean(),
                TextColumn::make('motivo')->limit(40),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
