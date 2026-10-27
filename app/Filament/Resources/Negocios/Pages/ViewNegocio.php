<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Pages;

use App\Filament\Resources\Negocios\NegocioResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNegocio extends ViewRecord
{
    protected static string $resource = NegocioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
