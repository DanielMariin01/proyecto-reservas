<?php

declare(strict_types=1);

namespace App\Estados\Cita;

use App\Enums\EstadoCitaValor;

final class EstadoPendiente implements EstadoCita
{
    public function valor(): EstadoCitaValor
    {
        return EstadoCitaValor::Pendiente;
    }

    public function puedeTransicionarA(EstadoCitaValor $destino): bool
    {
        return in_array($destino, [
            EstadoCitaValor::Confirmada,
            EstadoCitaValor::Cancelada,
            EstadoCitaValor::Reprogramada,
        ], true);
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
