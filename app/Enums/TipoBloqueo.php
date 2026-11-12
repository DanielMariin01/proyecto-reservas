<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoBloqueo: string
{
    /** Quita disponibilidad dentro del horario normal (RF-DIS-02/03). */
    case Bloqueo = 'bloqueo';

    /** Agrega disponibilidad fuera del horario normal, ej. abrir un domingo (RF-DIS-02). */
    case Apertura = 'apertura';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Bloqueo => 'Bloqueo (no atiendo)',
            self::Apertura => 'Apertura especial',
        };
    }
}
