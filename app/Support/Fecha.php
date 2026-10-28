<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonInterface;

final class Fecha
{
    /**
     * @var list<string>
     */
    private const DIAS = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];

    /**
     * @var list<string>
     */
    private const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    /**
     * Formatea una fecha en el estilo del proyecto: formatear(..., 'America/Bogota') === 'mar 27 oct'.
     */
    public static function formatear(CarbonInterface $momento, string $zonaHoraria): string
    {
        $local = $momento->setTimezone($zonaHoraria);
        $dia = self::DIAS[$local->dayOfWeek];
        $mes = self::MESES[$local->month - 1];

        return "{$dia} {$local->day} {$mes}";
    }
}
