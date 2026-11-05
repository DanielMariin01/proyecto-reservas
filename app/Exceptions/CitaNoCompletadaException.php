<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class CitaNoCompletadaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Solo se puede registrar el pago de una cita completada.');
    }
}
