<?php

declare(strict_types=1);

namespace App\Resenas;

use App\Models\Cita;

final class CadenaReglasResena
{
    /**
     * @param  list<ReglaResena>  $reglas
     */
    public function __construct(
        private readonly array $reglas,
    ) {}

    public function permite(Cita $cita): bool
    {
        foreach ($this->reglas as $regla) {
            if (! $regla->permite($cita)) {
                return false;
            }
        }

        return true;
    }
}
