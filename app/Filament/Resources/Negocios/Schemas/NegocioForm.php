<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Schemas;

use App\Enums\TipoNegocio;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                    Select::make('paleta_marca_id')
                        ->label('Paleta de marca')
                        ->relationship('paletaMarca', 'nombre')
                        ->searchable()
                        ->preload()
                        ->helperText('Define los colores de la página pública del negocio. Las paletas se administran en Paletas de marca.'),
                    Toggle::make('permite_editar_pagina')
                        ->label('El dueño puede editar los textos y fotos de su página')
                        ->helperText('RF-PAG-01: la estructura de bloques y los colores siguen siendo solo del super admin.')
                        ->default(false),
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
            Section::make('Página web')
                ->description('RF-BLQ-01/03: agrega, quita y reordena los bloques. El dueño solo puede editar los textos y fotos de los que ya dejaste aquí, si lo permites arriba.')
                ->visibleOn('edit')
                ->relationship('pagina')
                ->components([
                    Builder::make('bloques')
                        ->label('Bloques')
                        ->blocks(EsquemaBloquesPagina::blocks())
                        ->default([])
                        ->addActionLabel('Agregar bloque')
                        ->collapsible()
                        ->collapsed()
                        ->blockNumbers(false),
                ]),
        ]);
    }
}
