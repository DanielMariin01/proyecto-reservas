<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\Dinero;
use RuntimeException;

final class MontoDePagoIncorrectoException extends RuntimeException
{
    public function __construct(int $esperado, int $recibido)
    {
        parent::__construct(
            'El monto pagado ('.Dinero::formatearPesos($recibido).') no coincide con el total a cobrar ('.Dinero::formatearPesos($esperado).').',
        );
    }
}
