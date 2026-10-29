<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaletaMarcas\Pages;

use App\Filament\Resources\PaletaMarcas\PaletaMarcaResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaletaMarca extends CreateRecord
{
    protected static string $resource = PaletaMarcaResource::class;
}
