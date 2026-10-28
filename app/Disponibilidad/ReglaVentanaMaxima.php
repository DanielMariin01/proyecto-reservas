<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Models\Negocio;
use App\Models\Servicio;
use Carbon\CarbonImmutable;

/**
 * Sección 5, paso 7: descarta franjas en días posteriores a
 * hoy + ventana máxima del negocio.
 */
final class ReglaVentanaMaxima implements ReglaFranja
{
    public function permite(Franja $franja, Servicio $servicio, CarbonImmutable $ahora): bool
    {
        $negocio = Negocio::findOrFail($servicio->negocio_id);
        $limite = $ahora->addDays($negocio->ventana_maxima_dias);

        return $franja->ocupacionInicio->lessThanOrEqualTo($limite);
    }
}
