<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * La hora pedida nunca fue una franja válida según la sección 5 (fuera del
 * horario, no cabe con los márgenes, antes del aviso mínimo, etc.).
 */
final class HoraInvalidaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Esa hora no está disponible.');
    }
}
