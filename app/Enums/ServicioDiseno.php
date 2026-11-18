<?php

declare(strict_types=1);

namespace App\Enums;

enum ServicioDiseno: string
{
    case Tradicional = 'tradicional';
    case Semipermanente = 'semipermanente';
    case Rubber = 'rubber';
    case Dipping = 'dipping';
    case Gel = 'gel';
    case PressOn = 'press_on';
    case Acrilico = 'acrilico';
    case Polygel = 'polygel';
    case Pedicure = 'pedicure';
    case Retiro = 'retiro';
    case Reparacion = 'reparacion';
    case Otro = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Tradicional => 'Tradicional',
            self::Semipermanente => 'Semipermanente',
            self::Rubber => 'Rubber',
            self::Dipping => 'Dipping',
            self::Gel => 'Gel/Sof Gel',
            self::PressOn => 'Press On',
            self::Acrilico => 'Acrílico',
            self::Polygel => 'Polygel',
            self::Pedicure => 'Pedicure',
            self::Retiro => 'Retiro',
            self::Reparacion => 'Reparación',
            self::Otro => 'Otro',
        };
    }
}
