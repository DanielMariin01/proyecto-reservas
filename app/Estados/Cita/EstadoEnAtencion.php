<?php

declare(strict_types=1);

namespace App\Estados\Cita;

use App\Enums\EstadoCitaValor;

final class EstadoEnAtencion implements EstadoCita
{
    public function valor(): EstadoCitaValor
    {
        return EstadoCitaValor::EnAtencion;
    }

    public function puedeTransicionarA(EstadoCitaValor $destino): bool
    {
        return in_array($destino, [
            EstadoCitaValor::Completada,
            EstadoCitaValor::Cancelada,
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
