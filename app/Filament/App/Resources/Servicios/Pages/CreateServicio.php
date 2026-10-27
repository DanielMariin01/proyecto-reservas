<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Servicios\Pages;

use App\Filament\App\Resources\Servicios\ServicioResource;
use App\Models\Negocio;
use Filament\Resources\Pages\CreateRecord;

class CreateServicio extends CreateRecord
{
    protected static string $resource = ServicioResource::class;

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
