<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\PagoSuscripcion;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class PagoSuscripcionRegistrado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Negocio $negocio,
        public readonly PagoSuscripcion $pago,
        public readonly ?Usuario $registradoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'negocio.pago_suscripcion_registrado',
            negocio: $this->negocio,
            usuario: $this->registradoPor,
            modelo: PagoSuscripcion::class,
            modeloId: $this->pago->id,
            valoresDespues: $this->pago->only(['valor', 'metodo', 'periodo_desde', 'periodo_hasta']),
        );
    }
}
