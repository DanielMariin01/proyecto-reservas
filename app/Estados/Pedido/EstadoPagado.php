<?php

declare(strict_types=1);

namespace App\Estados\Pedido;

use App\Enums\EstadoPedidoValor;

final class EstadoPagado implements EstadoPedido
{
    public function valor(): EstadoPedidoValor
    {
        return EstadoPedidoValor::Pagado;
    }

    public function puedeTransicionarA(EstadoPedidoValor $destino): bool
    {
        return in_array($destino, [EstadoPedidoValor::Entregado, EstadoPedidoValor::Cancelado], true);
    }

    public function esFinal(): bool
    {
        return false;
    }
}
