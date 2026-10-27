<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Usuario;

final readonly class DatosInvitacionUsuario
{
    public function __construct(
        public Negocio $negocio,
        public string $nombre,
        public string $email,
        public RolNegocio $rol,
        public ?Usuario $invitadoPor = null,
    ) {}
}
