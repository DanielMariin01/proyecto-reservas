<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class CitaCancelada implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Cita $cita,
        public readonly ?Usuario $canceladaPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'cita.cancelada',
            negocio: Negocio::find($this->cita->negocio_id),
            usuario: $this->canceladaPor,
            modelo: Cita::class,
            modeloId: $this->cita->id,
            motivo: $this->cita->motivo_cancelacion,
        );
    }
}
