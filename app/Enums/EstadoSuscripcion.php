<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoSuscripcion: string
{
    case Activo = 'activo';
    case Vencido = 'vencido';
    case Suspendido = 'suspendido';
    case Cancelado = 'cancelado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Vencido => 'Vencido (en gracia)',
            self::Suspendido => 'Suspendido',
            self::Cancelado => 'Cancelado',
        };
    }

    /**
     * El siguiente estado al que pasa automáticamente por el paso del tiempo,
     * o null si ya es un estado final para la tarea programada.
     */
    public function siguienteEstadoAutomatico(): ?self
    {
        return match ($this) {
            self::Activo => self::Vencido,
            self::Vencido => self::Suspendido,
            self::Suspendido => self::Cancelado,
            self::Cancelado => null,
        };
    }

    public function permiteAccesoAlPanel(): bool
    {
        return match ($this) {
            self::Activo, self::Vencido => true,
            self::Suspendido, self::Cancelado => false,
        };
    }

    public function permiteReservas(): bool
    {
        return $this->permiteAccesoAlPanel();
    }
}
