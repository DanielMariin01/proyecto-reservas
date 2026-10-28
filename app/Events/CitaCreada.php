<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Cita;
use App\Models\Negocio;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Patrón Observador: T7 conectará aquí el correo de confirmación sin tocar
 * ServicioReservas ni CrearReserva.
 */
final class CitaCreada implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Cita $cita,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'cita.creada',
            negocio: Negocio::find($this->cita->negocio_id),
            modelo: Cita::class,
            modeloId: $this->cita->id,
            valoresDespues: [
                'estado' => $this->cita->estado->value,
                'inicio' => $this->cita->inicio->toIso8601String(),
            ],
        );
    }
}
