<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class RangosDeHorarioSuperpuestosException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Hay rangos de horas que se superponen el mismo día. Revísalos antes de guardar.');
    }
}
