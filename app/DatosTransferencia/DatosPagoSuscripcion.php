<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Support\Carbon;

final readonly class DatosPagoSuscripcion
{
    public function __construct(
        public Negocio $negocio,
        public Carbon $fecha,
        public int $valor,
        public string $metodo,
        public Carbon $periodoDesde,
        public Carbon $periodoHasta,
        public ?Usuario $registradoPor = null,
    ) {}
}
