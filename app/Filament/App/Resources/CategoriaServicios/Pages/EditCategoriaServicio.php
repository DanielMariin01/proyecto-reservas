<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CategoriaServicios\Pages;

use App\Filament\App\Resources\CategoriaServicios\CategoriaServicioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaServicio extends EditRecord
{
    protected static string $resource = CategoriaServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
