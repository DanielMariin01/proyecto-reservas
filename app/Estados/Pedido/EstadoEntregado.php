<?php

declare(strict_types=1);

namespace App\Estados\Pedido;

use App\Enums\EstadoPedidoValor;

final class EstadoEntregado implements EstadoPedido
{
    public function valor(): EstadoPedidoValor
    {
        return EstadoPedidoValor::Entregado;
    }

    public function puedeTransicionarA(EstadoPedidoValor $destino): bool
    {
        return false;
    }

    public function esFinal(): bool
    {
        return true;
    }
}
