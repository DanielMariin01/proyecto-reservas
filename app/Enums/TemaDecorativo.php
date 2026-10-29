<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Fondo decorativo temático de `banner_oferta`, para celebraciones
 * especiales — activación manual del super admin (nunca depende de la
 * fecha del sistema). `Ninguno` deja el banner igual que siempre.
 */
enum TemaDecorativo: string
{
    case Ninguno = 'ninguno';
    case Halloween = 'halloween';
    case Navidad = 'navidad';
    case AmorAmistad = 'amor_amistad';
    case AnioNuevo = 'anio_nuevo';
    case DiaMadre = 'dia_madre';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Ninguno => 'Ninguno',
            self::Halloween => 'Halloween',
            self::Navidad => 'Navidad',
            self::AmorAmistad => 'Amor y amistad',
            self::AnioNuevo => 'Año nuevo',
            self::DiaMadre => 'Día de la madre',
        };
    }
}
