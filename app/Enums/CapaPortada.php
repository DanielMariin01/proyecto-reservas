<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * La capa semitransparente sobre la foto de fondo de `portada`, solo
 * para que el texto se lea bien sobre cualquier foto — no es el color
 * libre de bloque (sección 1 de la skill diseno-reservas), por eso es
 * un selector fijo de 2 opciones y no un `ColorPicker`.
 */
enum CapaPortada: string
{
    case Oscura = 'oscura';
    case Clara = 'clara';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Oscura => 'Oscura (foto clara, texto blanco)',
            self::Clara => 'Clara (foto oscura, texto oscuro)',
        };
    }
}
