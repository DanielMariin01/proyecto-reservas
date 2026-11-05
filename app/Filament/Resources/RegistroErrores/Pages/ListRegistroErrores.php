<?php

declare(strict_types=1);

namespace App\Filament\Resources\RegistroErrores\Pages;

use App\Filament\Resources\RegistroErrores\RegistroErrorResource;
use Filament\Resources\Pages\ListRecords;

class ListRegistroErrores extends ListRecords
{
    protected static string $resource = RegistroErrorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
