<?php

declare(strict_types=1);

namespace App\Estados\Pedido;

use App\Enums\EstadoPedidoValor;

final class FabricaEstadoPedido
{
    public function paraValor(EstadoPedidoValor $valor): EstadoPedido
    {
        return match ($valor) {
            EstadoPedidoValor::Pendiente => new EstadoPendiente,
            EstadoPedidoValor::Pagado => new EstadoPagado,
            EstadoPedidoValor::Entregado => new EstadoEntregado,
            EstadoPedidoValor::Cancelado => new EstadoCancelado,
        };
    }
}
