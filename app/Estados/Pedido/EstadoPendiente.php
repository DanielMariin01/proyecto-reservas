<?php

declare(strict_types=1);

namespace App\Estados\Pedido;

use App\Enums\EstadoPedidoValor;

final class EstadoPendiente implements EstadoPedido
{
    public function valor(): EstadoPedidoValor
    {
        return EstadoPedidoValor::Pendiente;
    }

    public function puedeTransicionarA(EstadoPedidoValor $destino): bool
    {
        return in_array($destino, [EstadoPedidoValor::Pagado, EstadoPedidoValor::Cancelado], true);
    }

    public function esFinal(): bool
    {
        return false;
    }
}
