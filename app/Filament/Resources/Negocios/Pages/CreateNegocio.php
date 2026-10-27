<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Pages;

use App\Actions\CrearNegocio;
use App\DatosTransferencia\DatosNuevoNegocio;
use App\Enums\TipoNegocio;
use App\Filament\Resources\Negocios\NegocioResource;
use App\Models\Usuario;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNegocio extends CreateRecord
{
    protected static string $resource = NegocioResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Usuario $creadoPor */
        $creadoPor = Filament::auth()->user();

        return app(CrearNegocio::class)->ejecutar(
            new DatosNuevoNegocio(
                nombre: $data['nombre'],
                tipo: TipoNegocio::from($data['tipo']),
                nombreDueno: $data['nombre_dueno'],
                emailDueno: $data['email_dueno'],
                slug: $data['slug'] ?? null,
                direccion: $data['direccion'] ?? null,
                ciudad: $data['ciudad'] ?? null,
                telefono: $data['telefono'] ?? null,
                whatsapp: $data['whatsapp'] ?? null,
                zonaHoraria: $data['zona_horaria'] ?? 'America/Bogota',
            ),
            $creadoPor,
        );
    }
}
