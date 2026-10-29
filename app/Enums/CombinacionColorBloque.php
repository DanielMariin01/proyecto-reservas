<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Las únicas 3 combinaciones de fondo/texto que puede llevar un bloque
 * con texto libre (skill diseno-reservas, sección 1: contraste AA).
 * Nunca un color suelto — el super admin elige una de estas al armar el
 * bloque en el Builder; el dueño nunca la cambia.
 */
enum CombinacionColorBloque: string
{
    case Clara = 'clara';
    case Oscura = 'oscura';
    case Marca = 'marca';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Clara => 'Clara (fondo blanco, texto oscuro)',
            self::Oscura => 'Oscura (fondo oscuro, texto blanco)',
            self::Marca => 'Marca (fondo de la paleta del negocio, texto blanco)',
        };
    }
}
