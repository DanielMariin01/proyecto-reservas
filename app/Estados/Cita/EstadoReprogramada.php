<?php

declare(strict_types=1);

namespace App\Estados\Cita;

use App\Enums\EstadoCitaValor;

/**
 * Sección 6: "una cita reprogramada vuelve a quedar confirmada". Nunca
 * debería persistir más allá de un instante: ReprogramarCita pasa por aquí
 * y en la misma transacción deja la cita en Confirmada.
 */
final class EstadoReprogramada implements EstadoCita
{
    public function valor(): EstadoCitaValor
    {
        return EstadoCitaValor::Reprogramada;
    }

    public function puedeTransicionarA(EstadoCitaValor $destino): bool
    {
        return $destino === EstadoCitaValor::Confirmada;
    }

    public function estaActiva(): bool
    {
        return true;
    }

    public function esFinal(): bool
    {
        return false;
    }
}
