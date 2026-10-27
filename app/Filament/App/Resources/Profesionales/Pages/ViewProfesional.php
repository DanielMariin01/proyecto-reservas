<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Pages;

use App\Filament\App\Resources\Profesionales\ProfesionalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProfesional extends ViewRecord
{
    protected static string $resource = ProfesionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
