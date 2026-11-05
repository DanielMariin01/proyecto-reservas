<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class ResenaNoElegibleException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Ya no puedes dejar una reseña para esta cita.');
    }
}
