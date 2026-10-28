<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use Carbon\CarbonImmutable;

/**
 * Candidato de franja generado por una EstrategiaGeneracionFranjas, antes de
 * pasar por la CadenaReglasFranja. `ocupacionInicio` es el punto donde
 * empieza a consumirse el bloque (incluye el margen antes, sección 5 paso 6);
 * `finDelBloque` es el final del bloque libre del que salió, para que
 * ReglaCabeEnBloque pueda compararlo sin recibir nada más.
 */
final readonly class Franja
{
    public function __construct(
        public int $profesionalId,
        public CarbonImmutable $ocupacionInicio,
        public CarbonImmutable $finDelBloque,
    ) {}
}
