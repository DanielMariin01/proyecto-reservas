<?php

declare(strict_types=1);

namespace App\Estados\Cita;

use App\Enums\EstadoCitaValor;

final class EstadoCompletada implements EstadoCita
{
    public function valor(): EstadoCitaValor
    {
        return EstadoCitaValor::Completada;
    }

    public function puedeTransicionarA(EstadoCitaValor $destino): bool
    {
        return false;
    }

    public function estaActiva(): bool
    {
        return false;
    }

    public function esFinal(): bool
    {
        return true;
    }
}
