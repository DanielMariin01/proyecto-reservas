<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Sección 5: un mismo cliente no puede quedar con dos citas que se
 * traslapen en el tiempo, sin importar si son con el mismo profesional o
 * con dos distintos.
 */
final class ClienteYaTieneCitaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Este cliente ya tiene una cita a esa hora. Elige otra hora o cancela la cita existente.');
    }
}
