<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Pages;

use App\Filament\App\Actions\EditarHorarioSemanalAction;
use App\Filament\App\Resources\Profesionales\ProfesionalResource;
use App\Models\Profesional;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProfesional extends EditRecord
{
    protected static string $resource = ProfesionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditarHorarioSemanalAction::make($this->profesional(...)),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    private function profesional(): Profesional
    {
        /** @var Profesional $profesional */
        $profesional = $this->record;

        return $profesional;
    }
}
