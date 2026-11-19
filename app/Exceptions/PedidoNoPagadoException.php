<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class PedidoNoPagadoException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El pedido todavía no está marcado como pagado.');
    }
}
