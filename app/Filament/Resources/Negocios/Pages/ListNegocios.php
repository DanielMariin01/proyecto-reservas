<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Pages;

use App\Filament\Resources\Negocios\NegocioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNegocios extends ListRecords
{
    protected static string $resource = NegocioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
