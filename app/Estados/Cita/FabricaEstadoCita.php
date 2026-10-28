<?php

declare(strict_types=1);

namespace App\Estados\Cita;

use App\Enums\EstadoCitaValor;

final class FabricaEstadoCita
{
    public function paraValor(EstadoCitaValor $valor): EstadoCita
    {
        return match ($valor) {
            EstadoCitaValor::Pendiente => new EstadoPendiente,
            EstadoCitaValor::Confirmada => new EstadoConfirmada,
            EstadoCitaValor::EnAtencion => new EstadoEnAtencion,
            EstadoCitaValor::Completada => new EstadoCompletada,
            EstadoCitaValor::Cancelada => new EstadoCancelada,
            EstadoCitaValor::NoAsistio => new EstadoNoAsistio,
            EstadoCitaValor::Reprogramada => new EstadoReprogramada,
        };
    }

    /**
     * Los valores que siguen ocupando la franja (sección 5, paso 3): se
     * restan de la disponibilidad y cuentan para el choque de horarios.
     *
     * @return list<EstadoCitaValor>
     */
    public function valoresActivos(): array
    {
        return array_values(array_filter(
            EstadoCitaValor::cases(),
            fn (EstadoCitaValor $valor): bool => $this->paraValor($valor)->estaActiva(),
        ));
    }
}
