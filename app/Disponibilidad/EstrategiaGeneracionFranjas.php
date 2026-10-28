<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Support\Collection;

interface EstrategiaGeneracionFranjas
{
    /**
     * @param  Collection<int, BloqueLibre>  $bloques
     * @return Collection<int, Franja>
     */
    public function generar(Collection $bloques, Servicio $servicio, Profesional $profesional): Collection;
}
