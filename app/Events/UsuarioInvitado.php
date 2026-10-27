<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class UsuarioInvitado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Negocio $negocio,
        public readonly Usuario $usuarioInvitado,
        public readonly string $rol,
        public readonly ?Usuario $invitadoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'usuario.invitado',
            negocio: $this->negocio,
            usuario: $this->invitadoPor,
            modelo: Usuario::class,
            modeloId: $this->usuarioInvitado->id,
            valoresDespues: ['email' => $this->usuarioInvitado->email, 'rol' => $this->rol],
        );
    }
}
