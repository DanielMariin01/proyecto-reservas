<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonInterface;

final class Hora
{
    /**
     * Formatea una hora en el estilo del proyecto: formatear(..., 'America/Bogota') === '10:00 a. m.'.
     */
    public static function formatear(CarbonInterface $momento, string $zonaHoraria): string
    {
        $local = $momento->setTimezone($zonaHoraria);
        $periodo = $local->hour < 12 ? 'a. m.' : 'p. m.';

        return $local->format('g:i').' '.$periodo;
    }
}
