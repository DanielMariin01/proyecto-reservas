<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class NegocioSuplantado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Negocio $negocio,
        public readonly Usuario $superAdmin,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'negocio.suplantado',
            negocio: $this->negocio,
            usuario: $this->superAdmin,
            modelo: Negocio::class,
            modeloId: $this->negocio->id,
        );
    }
}
