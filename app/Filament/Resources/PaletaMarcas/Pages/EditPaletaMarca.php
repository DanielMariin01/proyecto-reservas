<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaletaMarcas\Pages;

use App\Filament\Resources\PaletaMarcas\PaletaMarcaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaletaMarca extends EditRecord
{
    protected static string $resource = PaletaMarcaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
