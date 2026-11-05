<?php

declare(strict_types=1);

namespace App\Resenas;

use App\Models\Cita;
use Carbon\CarbonImmutable;

/**
 * Pasado este número de días desde que la cita terminó, el enlace del
 * correo deja de aceptar una reseña nueva.
 */
final class ReglaDentroDeVentanaDeResena implements ReglaResena
{
    private const DIAS_LIMITE = 60;

    public function permite(Cita $cita): bool
    {
        return CarbonImmutable::now()->lessThanOrEqualTo($cita->fin->addDays(self::DIAS_LIMITE));
    }
}
