<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoResenaValor: string implements HasColor, HasLabel
{
    case Publicada = 'publicada';
    case Oculta = 'oculta';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Publicada => 'Publicada',
            self::Oculta => 'Oculta',
        };
    }

    public function getLabel(): string
    {
        return $this->etiqueta();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Publicada => 'success',
            self::Oculta => 'gray',
        };
    }
}
