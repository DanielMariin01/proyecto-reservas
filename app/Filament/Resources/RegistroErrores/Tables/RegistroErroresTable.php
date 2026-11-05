<?php

declare(strict_types=1);

namespace App\Filament\Resources\RegistroErrores\Tables;

use App\Models\Negocio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistroErroresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('negocio.nombre')->label('Negocio')->default('—'),
                TextColumn::make('usuario.nombre')->label('Usuario')->default('Sistema'),
                TextColumn::make('clase_excepcion')->label('Excepción')->searchable(),
                TextColumn::make('mensaje')->label('Mensaje')->limit(60)->wrap(),
                TextColumn::make('codigo_referencia')->label('Código')->badge()->color('gray'),
                TextColumn::make('estado_http')->label('HTTP')->sortable(),
            ])
            ->filters([
                SelectFilter::make('negocio_id')
                    ->label('Negocio')
                    ->options(fn () => Negocio::query()->orderBy('nombre')->pluck('nombre', 'id')),
                Filter::make('ultimas_24_horas')
                    ->label('Últimas 24 horas')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDay()))
                    ->default(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
