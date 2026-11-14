<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoEntregaValor: string
{
    case Recoger = 'recoger';
    case Domicilio = 'domicilio';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Recoger => 'Recoger en el local',
            self::Domicilio => 'Domicilio',
        };
    }
}
