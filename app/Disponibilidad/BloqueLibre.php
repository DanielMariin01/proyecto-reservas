<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use Carbon\CarbonImmutable;

/**
 * Un tramo continuo de tiempo (en UTC) donde el profesional está libre,
 * ya con las excepciones por fecha aplicadas (sección 5, pasos 1-2).
 */
final readonly class BloqueLibre
{
    public function __construct(
        public CarbonImmutable $inicio,
        public CarbonImmutable $fin,
    ) {}
}
