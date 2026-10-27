<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Pages;

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Filament\App\Resources\Profesionales\ProfesionalResource;
use App\Models\Negocio;
use App\Models\Usuario;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProfesional extends CreateRecord
{
    protected static string $resource = ProfesionalResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Usuario $creadoPor */
        $creadoPor = Filament::auth()->user();

        return app(CrearProfesional::class)->ejecutar(
            new DatosNuevoProfesional(
                negocio: Negocio::actual(),
                nombre: $data['nombre'],
                foto: $data['foto'] ?? null,
                especialidad: $data['especialidad'] ?? null,
                biografia: $data['biografia'] ?? null,
                certificaciones: $data['certificaciones'] ?? null,
                color: $data['color'] ?? null,
                nombreUsuario: $data['nombre_usuario'] ?? null,
                emailUsuario: $data['email_usuario'] ?? null,
            ),
            $creadoPor,
        );
    }
}
