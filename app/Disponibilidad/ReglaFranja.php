<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Models\Servicio;
use Carbon\CarbonImmutable;

interface ReglaFranja
{
    public function permite(Franja $franja, Servicio $servicio, CarbonImmutable $ahora): bool;
}
