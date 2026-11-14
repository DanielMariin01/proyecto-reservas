<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

final readonly class DatosPopularidadServicio
{
    public function __construct(
        public int $servicioId,
        public int $puesto,
        public int $totalReservas,
        public int $clientesDistintos,
    ) {}
}
