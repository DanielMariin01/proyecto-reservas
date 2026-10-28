<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrigenCita: string implements HasLabel
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

    public function getLabel(): string
    {
        return $this->etiqueta();
    }
}
