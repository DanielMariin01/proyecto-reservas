<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Schemas;

use App\Enums\TipoNegocio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NegocioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del negocio')
                ->columns(2)
                ->components([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),
                    TextInput::make('slug')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Si lo dejas vacío, se genera a partir del nombre.')
                        ->hiddenOn('edit'),
                    Select::make('tipo')
                        ->options([
                            TipoNegocio::Barberia->value => TipoNegocio::Barberia->etiqueta(),
                            TipoNegocio::Spa->value => TipoNegocio::Spa->etiqueta(),
                            TipoNegocio::Salon->value => TipoNegocio::Salon->etiqueta(),
                        ])
                        ->required(),
                    TextInput::make('zona_horaria')
                        ->default('America/Bogota')
                        ->required(),
                    TextInput::make('direccion')
                        ->maxLength(255),
                    TextInput::make('ciudad')
                        ->maxLength(255),
                    TextInput::make('telefono')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('whatsapp')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('color_primario')
                        ->maxLength(7),
                    TextInput::make('color_secundario')
                        ->maxLength(7),
                ]),
            Section::make('Dueño del negocio')
                ->description('Se crea un usuario con rol de dueño y se le envía un correo para que fije su contraseña.')
                ->columns(2)
                ->visibleOn('create')
                ->components([
                    TextInput::make('nombre_dueno')
                        ->label('Nombre')
                        ->required(),
                    TextInput::make('email_dueno')
                        ->label('Correo')
                        ->email()
                        ->required(),
                ]),
        ]);
    }
}
