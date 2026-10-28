<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Profesional;
use Carbon\CarbonImmutable;

final readonly class DatosBloqueoRapido
{
    public function __construct(
        public Profesional $profesional,
        public CarbonImmutable $fecha,
        public string $horaInicio,
        public string $horaFin,
        public ?string $motivo = null,
    ) {}
}
