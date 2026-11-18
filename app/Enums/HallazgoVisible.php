<?php

declare(strict_types=1);

namespace App\Enums;

enum HallazgoVisible: string
{
    case Manchas = 'manchas';
    case Mordida = 'mordida';
    case Otro = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Manchas => 'Manchas/cambios de color',
            self::Mordida => 'Mordida',
            self::Otro => 'Otro',
        };
    }
}
