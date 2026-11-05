<?php

declare(strict_types=1);

namespace App\Resenas;

use App\Enums\EstadoCitaValor;
use App\Models\Cita;

/**
 * Solo un cliente con una cita ya completada puede reseñarla (evita
 * reseñas de citas que nunca ocurrieron).
 */
final class ReglaCitaCompletada implements ReglaResena
{
    public function permite(Cita $cita): bool
    {
        return $cita->estado === EstadoCitaValor::Completada;
    }
}
