<?php

declare(strict_types=1);

namespace App\Enums;

enum CondicionPiel: string
{
    case Normal = 'normal';
    case Seca = 'seca';
    case Sensible = 'sensible';
    case Irritada = 'irritada';
    case Herida = 'herida';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Seca => 'Seca',
            self::Sensible => 'Sensible',
            self::Irritada => 'Irritada',
            self::Herida => 'Herida/inflamación',
        };
    }
}
