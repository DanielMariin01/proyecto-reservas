<?php

declare(strict_types=1);

namespace App\Enums;

enum RangoFinanzas: string
{
    case Mes = 'mes';
    case QuinceDias = 'quince_dias';
    case Anio = 'anio';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Mes => 'Este mes',
            self::QuinceDias => 'Últimos 15 días',
            self::Anio => 'Este año',
        };
    }

    /**
     * Si la serie de tendencia se agrupa por día o por mes, según lo
     * largo del rango (un año en barras diarias serían 365 puntos).
     */
    public function agrupacionTendencia(): GranularidadTendencia
    {
        return match ($this) {
            self::Mes, self::QuinceDias => GranularidadTendencia::Dia,
            self::Anio => GranularidadTendencia::Mes,
        };
    }
}
