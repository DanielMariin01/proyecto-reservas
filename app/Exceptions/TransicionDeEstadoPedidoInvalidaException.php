<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\EstadoPedidoValor;
use RuntimeException;

final class TransicionDeEstadoPedidoInvalidaException extends RuntimeException
{
    public function __construct(EstadoPedidoValor $desde, EstadoPedidoValor $hasta)
    {
        parent::__construct("No se puede pasar un pedido de {$desde->etiqueta()} a {$hasta->etiqueta()}.");
    }
}
