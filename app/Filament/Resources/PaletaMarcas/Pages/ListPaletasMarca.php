<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaletaMarcas\Pages;

use App\Filament\Resources\PaletaMarcas\PaletaMarcaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaletasMarca extends ListRecords
{
    protected static string $resource = PaletaMarcaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
