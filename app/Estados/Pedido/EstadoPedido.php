<?php

declare(strict_types=1);

namespace App\Estados\Pedido;

use App\Enums\EstadoPedidoValor;

/**
 * Patrón Estado (GoF), igual que App\Estados\Cita: cada implementación
 * sabe a cuáles estados puede pasar el pedido desde ahí, en vez de
 * dispersar esa lógica en condicionales por todo el código.
 */
interface EstadoPedido
{
    public function valor(): EstadoPedidoValor;

    public function puedeTransicionarA(EstadoPedidoValor $destino): bool;

    /**
     * Si ya no puede cambiar de estado nunca más.
     */
    public function esFinal(): bool;
}
