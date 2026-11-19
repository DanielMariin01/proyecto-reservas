<?php

declare(strict_types=1);

namespace App\Enums;

enum LargoUna: string
{
    case Corto = 'corto';
    case Medio = 'medio';
    case Largo = 'largo';
    case ExtraLargo = 'extra_largo';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Corto => 'Corto',
            self::Medio => 'Medio',
            self::Largo => 'Largo',
            self::ExtraLargo => 'Extra largo',
        };
    }
}
