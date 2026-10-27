<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Usuarios\Pages;

use App\Actions\InvitarUsuarioANegocio;
use App\DatosTransferencia\DatosInvitacionUsuario;
use App\Enums\RolNegocio;
use App\Filament\App\Resources\Usuarios\UsuarioResource;
use App\Models\Negocio;
use App\Models\Usuario;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUsuario extends CreateRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Usuario $invitadoPor */
        $invitadoPor = Filament::auth()->user();

        return app(InvitarUsuarioANegocio::class)->ejecutar(new DatosInvitacionUsuario(
            negocio: Negocio::actual(),
            nombre: $data['nombre'],
            email: $data['email'],
            rol: RolNegocio::from($data['rol']),
            invitadoPor: $invitadoPor,
        ));
    }
}
