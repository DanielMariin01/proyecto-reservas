<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfesionalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del profesional')
                ->columns(2)
                ->components([
                    TextInput::make('nombre')->required()->maxLength(255),
                    TextInput::make('especialidad')->maxLength(255),
                    FileUpload::make('foto')
                        ->image()
                        ->disk('public')
                        ->directory('profesionales')
                        ->columnSpanFull(),
                    Textarea::make('biografia')->label('Biografía')->columnSpanFull(),
                    Textarea::make('certificaciones')->columnSpanFull(),
                    ColorPicker::make('color')->default('#673DE6'),
                    Toggle::make('activo')->default(true),
                ]),
            Section::make('Usuario para entrar al panel')
                ->description('Opcional: crea un usuario con rol de profesional y le envía un correo para que fije su contraseña.')
                ->columns(2)
                ->visibleOn('create')
                ->components([
                    TextInput::make('nombre_usuario')->label('Nombre'),
                    TextInput::make('email_usuario')->label('Correo')->email(),
                ]),
        ]);
    }
}
