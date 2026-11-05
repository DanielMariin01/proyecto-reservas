<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Cita;

final readonly class DatosNuevaResena
{
    public function __construct(
        public Cita $cita,
        public int $calificacion,
        public string $comentario,
    ) {}
}
