<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Citas\Schemas;

use App\Enums\Permiso;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CitaForm
{
    public static function configure(Schema $schema): Schema
    {
        $negocio = Negocio::actual();

        return $schema->components([
            Section::make('Cita')
                ->columns(2)
                ->components([
                    Select::make('profesional_id')
                        ->label('Profesional')
                        ->placeholder('Cualquiera disponible')
                        ->options(fn () => Profesional::query()
                            ->where('negocio_id', $negocio->id)
                            ->where('activo', true)
                            ->pluck('nombre', 'id'))
                        ->searchable(),
                    Select::make('servicio_id')
                        ->label('Servicio')
                        ->required()
                        ->options(fn () => Servicio::query()
                            ->where('negocio_id', $negocio->id)
                            ->where('visible', true)
                            ->pluck('nombre', 'id'))
                        ->searchable(),
                    DatePicker::make('fecha')->required(),
                    TimePicker::make('hora')->label('Hora')->seconds(false)->required(),
                ]),
            Section::make('Cliente')
                ->description('Si el celular ya existe en este negocio, se usan sus datos guardados.')
                ->columns(2)
                ->components([
                    TextInput::make('nombre_cliente')->label('Nombre')->required()->maxLength(255),
                    TextInput::make('celular_cliente')->label('Celular')->required()->tel()->maxLength(20),
                    TextInput::make('correo_cliente')->label('Correo')->email()->maxLength(255),
                ]),
            Section::make('Notas y excepciones')
                ->columns(1)
                ->components([
                    Textarea::make('notas')->label('Nota para el profesional'),
                    Toggle::make('forzar')
                        ->label('Forzar (guardar aunque la hora esté fuera del horario o ya ocupada)')
                        ->default(false)
                        ->visible(fn (): bool => self::puedeForzar()),
                ]),
        ]);
    }

    private static function puedeForzar(): bool
    {
        $usuario = Filament::auth()->user();

        return $usuario !== null && ($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value));
    }
}
