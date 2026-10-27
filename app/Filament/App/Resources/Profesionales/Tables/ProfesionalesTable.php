<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Tables;

use App\Actions\VincularUsuarioAProfesional;
use App\Models\Profesional;
use App\Models\Usuario;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProfesionalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')->disk('public')->circular(),
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('especialidad'),
                TextColumn::make('usuario.email')->label('Usuario')->default('Sin vincular'),
                ToggleColumn::make('activo'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                self::accionVincularUsuario(),
            ])
            ->toolbarActions([]);
    }

    private static function accionVincularUsuario(): Action
    {
        return Action::make('vincularUsuario')
            ->label('Vincular usuario')
            ->icon('heroicon-o-user-plus')
            ->visible(fn (Profesional $record): bool => $record->usuario_id === null)
            ->schema([
                TextInput::make('nombre')->required(),
                TextInput::make('email')->email()->required(),
            ])
            ->action(function (Profesional $record, array $data): void {
                /** @var Usuario $invitadoPor */
                $invitadoPor = Filament::auth()->user();

                app(VincularUsuarioAProfesional::class)->ejecutar(
                    $record,
                    $data['nombre'],
                    $data['email'],
                    $invitadoPor,
                );

                Notification::make()->title('Usuario invitado')->success()->send();
            });
    }
}
