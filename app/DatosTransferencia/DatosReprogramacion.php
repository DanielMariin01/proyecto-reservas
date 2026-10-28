<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Cita;
use App\Models\Usuario;
use Carbon\CarbonImmutable;

final readonly class DatosReprogramacion
{
    public function __construct(
        public Cita $cita,
        public CarbonImmutable $nuevoInicio,
        public ?Usuario $reprogramadaPor = null,
        public bool $forzar = false,
    ) {}
}
