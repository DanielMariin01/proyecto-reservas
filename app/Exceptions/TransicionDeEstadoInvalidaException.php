<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\EstadoCitaValor;
use RuntimeException;

final class TransicionDeEstadoInvalidaException extends RuntimeException
{
    public function __construct(EstadoCitaValor $desde, EstadoCitaValor $hasta)
    {
        parent::__construct("No se puede pasar una cita de {$desde->etiqueta()} a {$hasta->etiqueta()}.");
    }
}
