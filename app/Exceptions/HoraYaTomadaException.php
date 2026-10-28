<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * La transacción perdió la carrera: otra reserva ya ocupó la franja antes
 * de que esta terminara de verificar y guardar (sección 5: "bloqueo
 * optimista al confirmar").
 */
final class HoraYaTomadaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Esta hora ya fue tomada. Elige otra.');
    }
}
