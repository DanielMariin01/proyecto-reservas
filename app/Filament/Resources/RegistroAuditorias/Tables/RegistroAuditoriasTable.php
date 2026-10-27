<?php

declare(strict_types=1);

namespace App\Filament\Resources\RegistroAuditorias\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistroAuditoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('negocio.nombre')->label('Negocio')->default('—'),
                TextColumn::make('usuario.nombre')->label('Usuario')->default('Sistema'),
                TextColumn::make('accion')->searchable(),
                IconColumn::make('hecho_por_soporte')->label('Soporte')->boolean(),
                TextColumn::make('motivo')->limit(50),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
