<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class PaletaDeMarcaEnUsoException extends RuntimeException
{
    public function __construct(int $cantidadDeNegocios)
    {
        $negocioONegocios = $cantidadDeNegocios === 1 ? 'negocio la está usando' : 'negocios la están usando';

        parent::__construct(
            "No puedes eliminar esta paleta: {$cantidadDeNegocios} {$negocioONegocios}. Cámbiales la paleta antes de eliminarla.",
        );
    }
}
