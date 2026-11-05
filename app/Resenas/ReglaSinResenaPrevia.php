<?php

declare(strict_types=1);

namespace App\Resenas;

use App\Models\Cita;
use App\Models\Resena;

/**
 * Una reseña por cita: evita que el mismo enlace de correo se use para
 * dejar varias reseñas de la misma cita.
 */
final class ReglaSinResenaPrevia implements ReglaResena
{
    public function permite(Cita $cita): bool
    {
        return Resena::query()->where('cita_id', $cita->id)->doesntExist();
    }
}
