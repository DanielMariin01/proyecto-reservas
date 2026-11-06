<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\CuentaCliente;
use Illuminate\Foundation\Events\Dispatchable;

final class CuentaClienteCreada implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly CuentaCliente $cuenta,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'cuenta_cliente.creada',
            modelo: CuentaCliente::class,
            modeloId: $this->cuenta->id,
            valoresDespues: ['correo' => $this->cuenta->correo],
        );
    }
}
