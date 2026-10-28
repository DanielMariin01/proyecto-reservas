<?php

declare(strict_types=1);

namespace App\Estados\Cita;

use App\Enums\EstadoCitaValor;

/**
 * Patrón Estado (GoF): cada implementación sabe a cuáles estados puede
 * pasar la cita desde ahí, en vez de dispersar esa lógica en condicionales
 * por todo el código (CambiarEstadoCita, CancelarCita, ReprogramarCita).
 */
interface EstadoCita
{
    public function valor(): EstadoCitaValor;

    public function puedeTransicionarA(EstadoCitaValor $destino): bool;

    /**
     * Si la cita sigue ocupando la franja (se resta de la disponibilidad y
     * cuenta para la verificación de choque de la sección 5).
     */
    public function estaActiva(): bool;

    /**
     * Si ya no puede cambiar de estado nunca más.
     */
    public function esFinal(): bool;
}
