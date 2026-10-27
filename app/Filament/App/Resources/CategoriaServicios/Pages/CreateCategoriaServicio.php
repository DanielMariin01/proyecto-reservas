<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CategoriaServicios\Pages;

use App\Filament\App\Resources\CategoriaServicios\CategoriaServicioResource;
use App\Models\Negocio;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoriaServicio extends CreateRecord
{
    protected static string $resource = CategoriaServicioResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['negocio_id'] = Negocio::actual()->id;

        return $data;
    }
}
