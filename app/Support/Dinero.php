<?php

declare(strict_types=1);

namespace App\Support;

final class Dinero
{
    /**
     * Formatea un valor en pesos colombianos enteros con el separador de
     * miles del proyecto: formatearPesos(24000) === '$24.000'.
     */
    public static function formatearPesos(int $valorEnPesos): string
    {
        return '$'.number_format($valorEnPesos, 0, ',', '.');
    }
}
