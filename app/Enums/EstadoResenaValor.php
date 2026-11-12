<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoResenaValor: string
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

    public function color(): string
    {
        return match ($this) {
            self::Publicada => 'success',
            self::Oculta => 'gray',
        };
    }
}
