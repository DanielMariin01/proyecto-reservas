<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrabajosFallidos\Pages;

use App\Filament\Resources\TrabajosFallidos\TrabajoFallidoResource;
use Filament\Resources\Pages\ListRecords;

class ListTrabajosFallidos extends ListRecords
{
    protected static string $resource = TrabajoFallidoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * RNF-OBS-03 también pide mostrar "sincronizaciones con Google
     * Calendar fallidas" aquí, pero esa integración es Fase 2 (sección
     * 14.4) y todavía no existe nada que listar.
     */
    public function getSubheading(): string
    {
        return 'Sincronización con Google Calendar: no disponible todavía (función de Fase 2).';
    }
}
