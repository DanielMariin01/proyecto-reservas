<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class CitaCambioDeEstado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Cita $cita,
        public readonly EstadoCitaValor $estadoAnterior,
        public readonly ?Usuario $cambiadoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'cita.cambio_de_estado',
            negocio: Negocio::find($this->cita->negocio_id),
            usuario: $this->cambiadoPor,
            modelo: Cita::class,
            modeloId: $this->cita->id,
            valoresAntes: ['estado' => $this->estadoAnterior->value],
            valoresDespues: ['estado' => $this->cita->estado->value],
        );
    }
}
