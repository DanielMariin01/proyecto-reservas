<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Pages;

use App\Filament\App\Resources\Profesionales\ProfesionalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProfesional extends EditRecord
{
    protected static string $resource = ProfesionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
