<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Resenas;

use App\Actions\CambiarVisibilidadResena;
use App\Actions\ResponderResena;
use App\Enums\EstadoResenaValor;
use App\Models\Resena;
use App\Models\Usuario;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Las acciones de una Resena (responder, ocultar, volver a publicar) —
 * una sola definición, igual que AccionesDeCita, compartida por la tabla
 * del recurso.
 */
final class AccionesDeResena
{
    public static function responder(): Action
    {
        return Action::make('responder')
            ->label(fn (Resena $record): string => $record->respuesta_texto === null ? 'Responder' : 'Editar respuesta')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('gray')
            ->visible(fn (Resena $record): bool => self::puedeGestionar($record))
            ->schema([
                Textarea::make('respuesta')
                    ->label('Tu respuesta')
                    ->required()
                    ->maxLength(1000)
                    ->default(fn (Resena $record): ?string => $record->respuesta_texto),
            ])
            ->action(function (Resena $record, array $data): void {
                self::ejecutarOFallar(function () use ($record, $data): void {
                    app(ResponderResena::class)->ejecutar($record, $data['respuesta'], self::usuarioActual());
                }, 'Respuesta publicada');
            });
    }

    public static function ocultar(): Action
    {
        return Action::make('ocultar')
            ->label('Ocultar')
            ->color('danger')
            ->icon(Heroicon::OutlinedEyeSlash)
            ->requiresConfirmation()
            ->visible(fn (Resena $record): bool => self::puedeGestionar($record) && $record->estaPublicada())
            ->action(function (Resena $record): void {
                self::ejecutarOFallar(function () use ($record): void {
                    app(CambiarVisibilidadResena::class)->ejecutar($record, EstadoResenaValor::Oculta, self::usuarioActual());
                }, 'Reseña oculta');
            });
    }

    public static function mostrar(): Action
    {
        return Action::make('mostrar')
            ->label('Volver a publicar')
            ->color('gray')
            ->icon(Heroicon::OutlinedEye)
            ->visible(fn (Resena $record): bool => self::puedeGestionar($record) && ! $record->estaPublicada())
            ->action(function (Resena $record): void {
                self::ejecutarOFallar(function () use ($record): void {
                    app(CambiarVisibilidadResena::class)->ejecutar($record, EstadoResenaValor::Publicada, self::usuarioActual());
                }, 'Reseña publicada de nuevo');
            });
    }

    private static function puedeGestionar(Resena $resena): bool
    {
        $usuario = Auth::guard('web')->user();

        return $usuario !== null && $usuario->can('update', $resena);
    }

    /**
     * Las acciones que la llaman solo son visibles cuando puedeGestionar()
     * ya confirmó que hay un usuario autenticado; esto solo lo deja claro
     * para Larastan.
     */
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
