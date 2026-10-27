<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CategoriaServicios\Pages;

use App\Filament\App\Resources\CategoriaServicios\CategoriaServicioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaServicios extends ListRecords
{
    protected static string $resource = CategoriaServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
