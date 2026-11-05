<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class PagoRegistrado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Cita $cita,
        public readonly Pago $pago,
        public readonly ?Usuario $registradoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'cita.pago_registrado',
            negocio: Negocio::find($this->cita->negocio_id),
            usuario: $this->registradoPor,
            modelo: Pago::class,
            modeloId: $this->pago->id,
            valoresDespues: [
                'metodo' => $this->pago->metodo->value,
                'valor' => $this->pago->valor,
                'propina' => $this->pago->propina,
            ],
        );
    }
}
