<?php

declare(strict_types=1);

namespace App\Resenas;

use App\Models\Cita;

/**
 * Patrón Cadena de reglas (igual que App\Disponibilidad\ReglaFranja):
 * cada regla sabe una sola condición para dejar una reseña, sin mezclar
 * las demás.
 */
interface ReglaResena
{
    public function permite(Cita $cita): bool;
}
