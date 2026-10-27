<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosInvitacionUsuario;
use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;

final class VincularUsuarioAProfesional
{
    public function __construct(
        private readonly InvitarUsuarioANegocio $invitarUsuarioANegocio,
    ) {}

    public function ejecutar(
        Profesional $profesional,
        string $nombre,
        string $email,
        ?Usuario $invitadoPor = null,
    ): void {
        $usuario = $this->invitarUsuarioANegocio->ejecutar(new DatosInvitacionUsuario(
            negocio: Negocio::findOrFail($profesional->negocio_id),
            nombre: $nombre,
            email: $email,
            rol: RolNegocio::Profesional,
            invitadoPor: $invitadoPor,
        ));

        $profesional->update(['usuario_id' => $usuario->id]);
    }
}
