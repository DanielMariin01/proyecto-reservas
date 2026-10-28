<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Coincide exactamente con el valor que devuelve Carbon::dayOfWeek
 * (0 = domingo ... 6 = sábado), para no tener que traducir índices.
 */
enum DiaSemana: int implements HasLabel
{
    case Domingo = 0;
    case Lunes = 1;
    case Martes = 2;
    case Miercoles = 3;
    case Jueves = 4;
    case Viernes = 5;
    case Sabado = 6;

    public function etiqueta(): string
    {
        return match ($this) {
            self::Domingo => 'Domingo',
            self::Lunes => 'Lunes',
            self::Martes => 'Martes',
            self::Miercoles => 'Miércoles',
            self::Jueves => 'Jueves',
            self::Viernes => 'Viernes',
            self::Sabado => 'Sábado',
        };
    }

    public function getLabel(): string
    {
        return $this->etiqueta();
    }
}
