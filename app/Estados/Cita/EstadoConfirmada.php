<?php

declare(strict_types=1);

namespace App\Estados\Cita;

use App\Enums\EstadoCitaValor;

final class EstadoConfirmada implements EstadoCita
{
    public function valor(): EstadoCitaValor
    {
        return EstadoCitaValor::Confirmada;
    }

    public function puedeTransicionarA(EstadoCitaValor $destino): bool
    {
        return in_array($destino, [
            EstadoCitaValor::EnAtencion,
            EstadoCitaValor::Cancelada,
            EstadoCitaValor::NoAsistio,
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
