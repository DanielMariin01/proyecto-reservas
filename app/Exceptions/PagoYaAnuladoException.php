<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class PagoYaAnuladoException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Este pago ya estaba anulado.');
    }
}
