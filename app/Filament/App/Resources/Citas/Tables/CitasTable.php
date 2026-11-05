<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Citas\Tables;

use App\Enums\EstadoCitaValor;
use App\Enums\Permiso;
use App\Filament\App\Resources\Citas\AccionesDeCita;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CitasTable
{
    public static function configure(Table $table): Table
    {
        $negocio = Negocio::actual();

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => self::aplicarAlcanceDelUsuario($query))
            ->defaultSort('inicio', 'desc')
            ->columns([
                TextColumn::make('inicio')->label('Fecha y hora')->dateTime('d/m/Y H:i', $negocio->zona_horaria)->sortable(),
                TextColumn::make('profesional.nombre')->label('Profesional')->sortable(),
                TextColumn::make('servicio.nombre')->label('Servicio'),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable(),
                TextColumn::make('estado')->badge(),
                TextColumn::make('origen'),
            ])
            ->filters([
                SelectFilter::make('profesional_id')
                    ->label('Profesional')
                    ->options(fn () => Profesional::query()->where('negocio_id', $negocio->id)->pluck('nombre', 'id')),
                SelectFilter::make('estado')
                    ->options(fn () => Collection::make(EstadoCitaValor::cases())->mapWithKeys(fn (EstadoCitaValor $estado): array => [$estado->value => $estado->etiqueta()])),
            ])
            ->recordActions([
                AccionesDeCita::completarConPago(),
                AccionesDeCita::cambiarEstado(),
                AccionesDeCita::reprogramar(),
                AccionesDeCita::cancelar(),
                AccionesDeCita::anularPago(),
            ]);
    }

    /**
     * CU-04: un profesional sin el permiso de gestionar todas las citas
     * solo ve las suyas.
     *
     * @param  Builder<Cita>  $query
     * @return Builder<Cita>
     */
    private static function aplicarAlcanceDelUsuario(Builder $query): Builder
    {
        $usuario = Filament::auth()->user();

        if ($usuario !== null && ($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value))) {
            return $query;
        }

        $profesional = $usuario?->profesional;
        $profesionalId = $profesional === null ? 0 : $profesional->id;

        return $query->where('profesional_id', $profesionalId);
    }
}
