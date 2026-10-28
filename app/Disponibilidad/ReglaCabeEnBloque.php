<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Models\Servicio;
use Carbon\CarbonImmutable;

/**
 * Sección 5, paso 6: descarta la franja si inicio + margen previo +
 * duración + margen posterior supera el fin del bloque.
 */
final class ReglaCabeEnBloque implements ReglaFranja
{
    public function permite(Franja $franja, Servicio $servicio, CarbonImmutable $ahora): bool
    {
        $finOcupacion = $franja->ocupacionInicio
            ->addMinutes($servicio->margenAntesMinutos())
            ->addMinutes($servicio->duracion_minutos)
            ->addMinutes($servicio->margenDespuesMinutos());

        return $finOcupacion->lessThanOrEqualTo($franja->finDelBloque);
    }
}
