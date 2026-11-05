<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Resenas\Tables;

use App\Enums\EstadoResenaValor;
use App\Enums\Permiso;
use App\Filament\App\Resources\Resenas\AccionesDeResena;
use App\Models\Resena;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResenasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => self::aplicarAlcanceDelUsuario($query))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('cliente.nombre')->label('Cliente'),
                TextColumn::make('profesional.nombre')->label('Profesional'),
                TextColumn::make('calificacion')->label('Estrellas')->badge()->color('warning')->formatStateUsing(fn (int $state): string => "{$state} ★"),
                TextColumn::make('comentario')->label('Comentario')->limit(60)->wrap(),
                TextColumn::make('estado')->label('Estado')->badge(),
                TextColumn::make('respuesta_texto')->label('Respuesta')->limit(40)->placeholder('Sin responder'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        EstadoResenaValor::Publicada->value => EstadoResenaValor::Publicada->etiqueta(),
                        EstadoResenaValor::Oculta->value => EstadoResenaValor::Oculta->etiqueta(),
                    ]),
                SelectFilter::make('calificacion')
                    ->label('Estrellas')
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
            ])
            ->recordActions([
                AccionesDeResena::responder(),
                AccionesDeResena::ocultar(),
                AccionesDeResena::mostrar(),
            ])
            ->toolbarActions([]);
    }

    /**
     * Un profesional sin el permiso de gestionar reseñas solo ve las suyas
     * (igual que CitasTable::aplicarAlcanceDelUsuario).
     *
     * @param  Builder<Resena>  $query
     * @return Builder<Resena>
     */
    private static function aplicarAlcanceDelUsuario(Builder $query): Builder
    {
        $usuario = Filament::auth()->user();

        if ($usuario !== null && ($usuario->es_super_admin || $usuario->can(Permiso::GestionarResenas->value))) {
            return $query;
        }

        $profesional = $usuario?->profesional;
        $profesionalId = $profesional === null ? 0 : $profesional->id;

        return $query->where('profesional_id', $profesionalId);
    }
}
