<?php

declare(strict_types=1);

namespace App\Enums;

enum AutorizacionFotos: string
{
    case SoloHistorialPrivado = 'solo_historial_privado';
    case AutorizoPublicacion = 'autorizo_publicacion';
    case NoAutorizo = 'no_autorizo';

    public function etiqueta(): string
    {
        return match ($this) {
            self::SoloHistorialPrivado => 'Solo para historial privado',
            self::AutorizoPublicacion => 'Autorizo además su publicación en redes y material publicitario',
            self::NoAutorizo => 'No autorizo fotografías',
        };
    }
}
