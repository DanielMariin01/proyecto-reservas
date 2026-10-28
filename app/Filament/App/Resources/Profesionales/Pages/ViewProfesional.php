<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Pages;

use App\Filament\App\Actions\EditarHorarioSemanalAction;
use App\Filament\App\Actions\VerVistaPreviaDisponibilidadAction;
use App\Filament\App\Resources\Profesionales\ProfesionalResource;
use App\Models\Profesional;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProfesional extends ViewRecord
{
    protected static string $resource = ProfesionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditarHorarioSemanalAction::make($this->profesional(...)),
            VerVistaPreviaDisponibilidadAction::make($this->profesional(...)),
            EditAction::make(),
        ];
    }

    private function profesional(): Profesional
    {
        /** @var Profesional $profesional */
        $profesional = $this->record;

        return $profesional;
    }
}
