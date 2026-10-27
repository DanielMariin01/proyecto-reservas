<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CategoriaServicios\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoriaServicioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')->required()->maxLength(255),
        ]);
    }
}
