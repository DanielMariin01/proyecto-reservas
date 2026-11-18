<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoNegocio: string
{
    case Barberia = 'barberia';
    case Spa = 'spa';
    case Salon = 'salon';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Barberia => 'Barbería',
            self::Spa => 'Spa',
            self::Salon => 'Salón',
        };
    }

    /**
     * RF-HCL-01: el módulo de historia clínica solo aplica a negocios de
     * este tipo. Queda aquí para que agregar otro tipo que también lo
     * necesite sea un cambio de una sola línea.
     */
    public function requiereHistoriaClinica(): bool
    {
        return $this === self::Spa;
    }
}
