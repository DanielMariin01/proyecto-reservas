<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Enums\EstadoPedidoValor;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class PedidoCambioDeEstado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Pedido $pedido,
        public readonly EstadoPedidoValor $estadoAnterior,
        public readonly ?Usuario $cambiadoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'pedido.cambio_de_estado',
            negocio: Negocio::find($this->pedido->negocio_id),
            usuario: $this->cambiadoPor,
            modelo: Pedido::class,
            modeloId: $this->pedido->id,
            valoresAntes: ['estado' => $this->estadoAnterior->value],
            valoresDespues: ['estado' => $this->pedido->estado->value],
        );
    }
}
