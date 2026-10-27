<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Servicios\Schemas;

use App\Models\CategoriaServicio;
use App\Models\Servicio;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServicioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Servicio')
                ->columns(2)
                ->components([
                    TextInput::make('nombre')->required()->maxLength(255),
                    Select::make('categoria_servicio_id')
                        ->label('Categoría')
                        ->options(fn () => CategoriaServicio::query()->pluck('nombre', 'id'))
                        ->searchable(),
                    Textarea::make('descripcion')->columnSpanFull(),
                    FileUpload::make('foto')
                        ->image()
                        ->disk('public')
                        ->directory('servicios')
                        ->columnSpanFull(),
                    TextInput::make('duracion_minutos')
                        ->label('Duración (minutos)')
                        ->numeric()
                        ->required()
                        ->minValue(Servicio::DURACION_MINIMA_MINUTOS),
                    TextInput::make('precio')->numeric()->required()->prefix('$'),
                    Toggle::make('visible')->default(true),
                ]),
            Section::make('Márgenes y franjas')
                ->description('Si se dejan vacíos, el servicio usa las reglas generales del negocio (se definen en la tarea de disponibilidad).')
                ->columns(3)
                ->components([
                    TextInput::make('margen_antes_minutos')->label('Margen antes (min)')->numeric(),
                    TextInput::make('margen_despues_minutos')->label('Margen después (min)')->numeric(),
                    TextInput::make('intervalo_franjas_minutos')->label('Intervalo de franjas (min)')->numeric(),
                ]),
        ]);
    }
}
