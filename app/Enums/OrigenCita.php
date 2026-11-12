<?php

declare(strict_types=1);

namespace App\Enums;

enum OrigenCita: string
{
    case Web = 'web';
    case Panel = 'panel';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Web => 'Página pública',
            self::Panel => 'Panel',
        };
    }
}
