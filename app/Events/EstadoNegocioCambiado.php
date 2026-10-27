<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class EstadoNegocioCambiado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Negocio $negocio,
        public readonly EstadoSuscripcion $estadoAnterior,
        public readonly EstadoSuscripcion $estadoNuevo,
        public readonly ?string $motivo = null,
        public readonly ?Usuario $cambiadoPor = null,
    ) {}

    public function esCambioManual(): bool
    {
        return $this->cambiadoPor !== null;
    }

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'negocio.estado_cambiado',
            negocio: $this->negocio,
            usuario: $this->cambiadoPor,
            modelo: Negocio::class,
            modeloId: $this->negocio->id,
            valoresAntes: ['estado_suscripcion' => $this->estadoAnterior->value],
            valoresDespues: ['estado_suscripcion' => $this->estadoNuevo->value],
            motivo: $this->motivo,
        );
    }
}
