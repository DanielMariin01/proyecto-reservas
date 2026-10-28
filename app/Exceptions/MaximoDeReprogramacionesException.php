<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class MaximoDeReprogramacionesException extends RuntimeException
{
    public function __construct(int $maximo)
    {
        parent::__construct("Esta cita ya se reprogramó el máximo de {$maximo} veces permitidas.");
    }
}
