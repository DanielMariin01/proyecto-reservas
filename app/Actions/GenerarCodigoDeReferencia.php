<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Str;

/**
 * RNF-OBS-07: el código de referencia que ve el usuario ("Algo falló.
 * Código REF-8F3K") se deriva del id_peticion en vez de guardarse en una
 * tabla aparte — así soporte va directo del código al log/la traza
 * exacta de esa petición.
 */
final class GenerarCodigoDeReferencia
{
    private const LONGITUD = 8;

    public function ejecutar(string $idPeticion): string
    {
        $sinGuiones = str_replace('-', '', $idPeticion);

        return 'REF-'.Str::upper(Str::substr($sinGuiones, 0, self::LONGITUD));
    }
}
