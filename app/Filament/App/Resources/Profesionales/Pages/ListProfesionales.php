<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Pages;

use App\Filament\App\Resources\Profesionales\ProfesionalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfesionales extends ListRecords
{
    protected static string $resource = ProfesionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
