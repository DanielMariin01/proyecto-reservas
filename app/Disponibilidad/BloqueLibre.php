<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

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

    /**
     * Quita el intervalo [$inicioOcupado, $finOcupado) de cada bloque,
     * partiéndolo en dos si cae en la mitad. Lo usan tanto los bloqueos
     * (sección 5, paso 2) como las citas activas (paso 3) para no duplicar
     * el mismo algoritmo de resta de intervalos.
     *
     * @param  Collection<int, self>  $bloques
     * @return Collection<int, self>
     */
    public static function restar(Collection $bloques, CarbonImmutable $inicioOcupado, CarbonImmutable $finOcupado): Collection
    {
        return $bloques->flatMap(function (self $bloque) use ($inicioOcupado, $finOcupado): array {
            if ($finOcupado->lessThanOrEqualTo($bloque->inicio) || $inicioOcupado->greaterThanOrEqualTo($bloque->fin)) {
                return [$bloque];
            }

            $restante = [];

            if ($inicioOcupado->greaterThan($bloque->inicio)) {
                $restante[] = new self($bloque->inicio, $inicioOcupado);
            }

            if ($finOcupado->lessThan($bloque->fin)) {
                $restante[] = new self($finOcupado, $bloque->fin);
            }

            return $restante;
        });
    }
}
