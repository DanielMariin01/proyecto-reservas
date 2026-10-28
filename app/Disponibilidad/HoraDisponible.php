<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Models\Servicio;
use Carbon\CarbonImmutable;

/**
 * Lo que ServicioDisponibilidad devuelve al mundo: la hora que ve el
 * cliente (ya corrido el margen antes) y cuándo termina el servicio
 * (sin el margen después, que es tiempo de limpieza, no parte de la cita).
 */
final readonly class HoraDisponible
{
    public function __construct(
        public int $profesionalId,
        public CarbonImmutable $inicio,
        public CarbonImmutable $fin,
    ) {}

    public static function desdeFranja(Franja $franja, Servicio $servicio): self
    {
        $inicio = $franja->ocupacionInicio->addMinutes($servicio->margenAntesMinutos());

        return new self(
            profesionalId: $franja->profesionalId,
            inicio: $inicio,
            fin: $inicio->addMinutes($servicio->duracion_minutos),
        );
    }
}
