<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class ContrasenaRestablecida implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Usuario $usuarioAfectado,
        public readonly ?Usuario $restablecidoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'usuario.contrasena_restablecida',
            usuario: $this->restablecidoPor,
            modelo: Usuario::class,
            modeloId: $this->usuarioAfectado->id,
        );
    }
}
