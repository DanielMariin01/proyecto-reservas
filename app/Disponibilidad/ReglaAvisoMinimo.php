<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Models\Servicio;
use Carbon\CarbonImmutable;

/**
 * Sección 5, paso 7: descarta franjas anteriores a ahora + aviso mínimo.
 * Se compara contra la hora que ve el cliente (ya con el margen antes
 * aplicado), no contra el inicio interno de ocupación del bloque.
 */
final class ReglaAvisoMinimo implements ReglaFranja
{
    public function permite(Franja $franja, Servicio $servicio, CarbonImmutable $ahora): bool
    {
        $horaVisible = $franja->ocupacionInicio->addMinutes($servicio->margenAntesMinutos());
        $limite = $ahora->addMinutes($servicio->avisoMinimoMinutos());

        return $horaVisible->greaterThanOrEqualTo($limite);
    }
}
