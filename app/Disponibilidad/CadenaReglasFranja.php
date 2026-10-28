<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Models\Servicio;
use Carbon\CarbonImmutable;

final class CadenaReglasFranja
{
    /**
     * @param  list<ReglaFranja>  $reglas
     */
    public function __construct(
        private readonly array $reglas,
    ) {}

    public function permite(Franja $franja, Servicio $servicio, CarbonImmutable $ahora): bool
    {
        foreach ($this->reglas as $regla) {
            if (! $regla->permite($franja, $servicio, $ahora)) {
                return false;
            }
        }

        return true;
    }
}
