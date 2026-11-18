<?php

declare(strict_types=1);

namespace App\Enums;

enum CondicionUna: string
{
    case SinAlteraciones = 'sin_alteraciones';
    case Fragil = 'fragil';
    case Delgada = 'delgada';
    case Estriada = 'estriada';
    case Descamada = 'descamada';
    case Desprendimiento = 'desprendimiento';

    public function etiqueta(): string
    {
        return match ($this) {
            self::SinAlteraciones => 'Sin alteraciones visibles',
            self::Fragil => 'Frágil',
            self::Delgada => 'Delgada',
            self::Estriada => 'Estriada',
            self::Descamada => 'Descamada',
            self::Desprendimiento => 'Desprendimiento',
        };
    }
}
