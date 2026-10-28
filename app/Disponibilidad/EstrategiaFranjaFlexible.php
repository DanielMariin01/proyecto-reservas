<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Disponibilidad\Concerns\GeneraFranjasPorPaso;
use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Support\Collection;

/**
 * Intervalo de franjas configurado explícito (sección 5): el paso es fijo
 * (ej. cada 15 min), sin importar cuánto dure el servicio.
 */
final class EstrategiaFranjaFlexible implements EstrategiaGeneracionFranjas
{
    use GeneraFranjasPorPaso;

    public function __construct(
        private readonly int $intervaloMinutos,
    ) {}

    /**
     * @param  Collection<int, BloqueLibre>  $bloques
     * @return Collection<int, Franja>
     */
    public function generar(Collection $bloques, Servicio $servicio, Profesional $profesional): Collection
    {
        return $this->generarConPaso($bloques, $profesional, $this->intervaloMinutos);
    }
}
