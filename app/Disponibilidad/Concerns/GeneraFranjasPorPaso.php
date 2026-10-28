<?php

declare(strict_types=1);

namespace App\Disponibilidad\Concerns;

use App\Disponibilidad\BloqueLibre;
use App\Disponibilidad\Franja;
use App\Models\Profesional;
use Illuminate\Support\Collection;

/**
 * Paso 5 de la sección 5: generar franjas desde el inicio de cada bloque
 * libre, avanzando por un paso en minutos. Lo único que cambia entre
 * EstrategiaFranjaPorDuracion y EstrategiaFranjaFlexible es ese paso.
 */
trait GeneraFranjasPorPaso
{
    /**
     * @param  Collection<int, BloqueLibre>  $bloques
     * @return Collection<int, Franja>
     */
    private function generarConPaso(Collection $bloques, Profesional $profesional, int $pasoMinutos): Collection
    {
        $franjas = collect();

        foreach ($bloques as $bloque) {
            $cursor = $bloque->inicio;

            while ($cursor->lessThan($bloque->fin)) {
                $franjas->push(new Franja($profesional->id, $cursor, $bloque->fin));
                $cursor = $cursor->addMinutes($pasoMinutos);
            }
        }

        return $franjas;
    }
}
