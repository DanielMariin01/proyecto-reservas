<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class PagoAnulado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Pago $pago,
        public readonly ?Usuario $anuladoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'pago.anulado',
            negocio: Negocio::find($this->pago->negocio_id),
            usuario: $this->anuladoPor,
            modelo: Pago::class,
            modeloId: $this->pago->id,
            motivo: $this->pago->motivo_anulacion,
        );
    }
}
