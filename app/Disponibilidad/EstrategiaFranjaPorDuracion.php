<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Disponibilidad\Concerns\GeneraFranjasPorPaso;
use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Support\Collection;

/**
 * Intervalo de franjas por defecto (sección 5): el paso es la propia
 * duración del servicio. Corte de 45 min -> franjas cada 45 min.
 */
final class EstrategiaFranjaPorDuracion implements EstrategiaGeneracionFranjas
{
    use GeneraFranjasPorPaso;

    /**
     * @param  Collection<int, BloqueLibre>  $bloques
     * @return Collection<int, Franja>
     */
    public function generar(Collection $bloques, Servicio $servicio, Profesional $profesional): Collection
    {
        return $this->generarConPaso($bloques, $profesional, $servicio->duracion_minutos);
    }
}
