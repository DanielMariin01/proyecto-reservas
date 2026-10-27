<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Tables;

use App\Actions\RestablecerContrasenaUsuario;
use App\Models\Usuario;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsuariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                IconColumn::make('es_super_admin')->label('Super admin')->boolean(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                self::accionRestablecerContrasena(),
            ])
            ->toolbarActions([]);
    }

    private static function accionRestablecerContrasena(): Action
    {
        return Action::make('restablecerContrasena')
            ->label('Restablecer contraseña')
            ->icon('heroicon-o-key')
            ->requiresConfirmation()
            ->modalDescription('Se le enviará un correo para que elija una nueva contraseña.')
            ->action(function (Usuario $record): void {
                /** @var Usuario $superAdmin */
                $superAdmin = Filament::auth()->user();

                app(RestablecerContrasenaUsuario::class)->ejecutar($record, $superAdmin);

                Notification::make()->title('Correo de restablecimiento enviado')->success()->send();
            });
    }
}
