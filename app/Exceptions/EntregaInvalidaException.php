<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * El pedido pide domicilio sin barrio, o cualquier otra combinación de
 * tipo de entrega y datos que no tiene sentido (RF-TIE-02).
 */
final class EntregaInvalidaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Para domicilio hace falta el barrio.');
    }
}
