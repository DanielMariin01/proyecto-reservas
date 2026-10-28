<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class FueraDePoliticaDeCancelacionException extends RuntimeException
{
    public function __construct(int $horasMinimas)
    {
        parent::__construct("Ya no se puede hacer este cambio: se necesitan al menos {$horasMinimas} horas de anticipación.");
    }
}
