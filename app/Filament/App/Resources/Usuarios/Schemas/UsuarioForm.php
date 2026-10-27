<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Usuarios\Schemas;

use App\Enums\RolNegocio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UsuarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')->required()->maxLength(255),
            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->disabledOn('edit'),
            TextInput::make('telefono')->tel()->maxLength(255)->hiddenOn('create'),
            Select::make('rol')
                ->label('Rol')
                ->options([
                    RolNegocio::Dueno->value => RolNegocio::Dueno->etiqueta(),
                    RolNegocio::Recepcion->value => RolNegocio::Recepcion->etiqueta(),
                    RolNegocio::Profesional->value => RolNegocio::Profesional->etiqueta(),
                ])
                ->required()
                ->dehydrated(),
        ]);
    }
}
