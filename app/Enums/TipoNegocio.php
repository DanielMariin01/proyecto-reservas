<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoNegocio: string
{
    case Barberia = 'barberia';
    case Spa = 'spa';
    case Salon = 'salon';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Barberia => 'Barbería',
            self::Spa => 'Spa',
            self::Salon => 'Salón',
        };
    }
}
