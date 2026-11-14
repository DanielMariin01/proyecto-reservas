<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Negocio;
use Carbon\CarbonImmutable;

final readonly class DatosNuevoGasto
{
    public function __construct(
        public Negocio $negocio,
        public int $monto,
        public string $descripcion,
        public CarbonImmutable $fecha,
    ) {}
}
