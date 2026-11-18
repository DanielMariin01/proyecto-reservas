<?php

declare(strict_types=1);

namespace App\Enums;

enum FormaUna: string
{
    case Cuadrada = 'cuadrada';
    case Squoval = 'squoval';
    case Redonda = 'redonda';
    case Ovalada = 'ovalada';
    case Almendrada = 'almendrada';
    case Coffin = 'coffin';
    case Ballerina = 'ballerina';
    case Stiletto = 'stiletto';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Cuadrada => 'Cuadrada',
            self::Squoval => 'Squoval',
            self::Redonda => 'Redonda',
            self::Ovalada => 'Ovalada',
            self::Almendrada => 'Almendrada',
            self::Coffin => 'Coffin',
            self::Ballerina => 'Ballerina',
            self::Stiletto => 'Stiletto',
        };
    }
}
