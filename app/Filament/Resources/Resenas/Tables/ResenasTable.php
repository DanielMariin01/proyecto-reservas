<?php

declare(strict_types=1);

namespace App\Filament\Resources\Resenas\Tables;

use App\Actions\CambiarVisibilidadResena;
use App\Enums\EstadoResenaValor;
use App\Models\Negocio;
use App\Models\Resena;
use App\Models\Usuario;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ResenasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('negocio.nombre')->label('Negocio'),
                TextColumn::make('cliente.nombre')->label('Cliente'),
                TextColumn::make('profesional.nombre')->label('Profesional'),
                TextColumn::make('calificacion')->label('Estrellas')->badge()->color('warning')->formatStateUsing(fn (int $state): string => "{$state} ★"),
                TextColumn::make('comentario')->label('Comentario')->limit(60)->wrap(),
                TextColumn::make('estado')->label('Estado')->badge(),
            ])
            ->filters([
                SelectFilter::make('negocio_id')
                    ->label('Negocio')
                    ->options(fn () => Negocio::query()->orderBy('nombre')->pluck('nombre', 'id')),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        EstadoResenaValor::Publicada->value => EstadoResenaValor::Publicada->etiqueta(),
                        EstadoResenaValor::Oculta->value => EstadoResenaValor::Oculta->etiqueta(),
                    ]),
            ])
            ->recordActions([
                self::ocultar(),
                self::mostrar(),
            ])
            ->toolbarActions([]);
    }

    private static function ocultar(): Action
    {
        return Action::make('ocultar')
            ->label('Ocultar')
            ->color('danger')
            ->icon(Heroicon::OutlinedEyeSlash)
            ->requiresConfirmation()
            ->visible(fn (Resena $record): bool => $record->estaPublicada())
            ->action(function (Resena $record): void {
                self::ejecutarOFallar(function () use ($record): void {
                    app(CambiarVisibilidadResena::class)->ejecutar($record, EstadoResenaValor::Oculta, self::usuarioActual());
                }, 'Reseña oculta');
            });
    }

    private static function mostrar(): Action
    {
        return Action::make('mostrar')
            ->label('Volver a publicar')
            ->color('gray')
            ->icon(Heroicon::OutlinedEye)
            ->visible(fn (Resena $record): bool => ! $record->estaPublicada())
            ->action(function (Resena $record): void {
                self::ejecutarOFallar(function () use ($record): void {
                    app(CambiarVisibilidadResena::class)->ejecutar($record, EstadoResenaValor::Publicada, self::usuarioActual());
                }, 'Reseña publicada de nuevo');
            });
    }

    private static function usuarioActual(): Usuario
    {
        $usuario = Auth::guard('web')->user();
        abort_if($usuario === null, 403);

        return $usuario;
    }

    private static function ejecutarOFallar(callable $accion, string $mensajeExito): void
    {
        try {
            $accion();
        } catch (Throwable $excepcion) {
            Notification::make()->danger()->title($excepcion->getMessage())->send();

            throw new Halt;
        }

        Notification::make()->success()->title($mensajeExito)->send();
    }
}
