<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;

/**
 * Agrupa lo que toda notificación de citas necesita leer, resuelto con
 * findOrFail en vez de la relación de Eloquent (igual que
 * RecursoCitaPublica y CancelarCita/ReprogramarCita en T5): evita que la
 * propiedad quede nullable para Larastan y da el dato fresco, no el que
 * haya quedado cacheado en el modelo serializado desde la cola.
 */
final readonly class DatosDeCitaParaNotificacion
{
    public function __construct(
        public Negocio $negocio,
        public Servicio $servicio,
        public Profesional $profesional,
        public Cliente $cliente,
    ) {}

    public static function desde(Cita $cita): self
    {
        return new self(
            negocio: Negocio::findOrFail($cita->negocio_id),
            servicio: Servicio::findOrFail($cita->servicio_id),
            profesional: Profesional::findOrFail($cita->profesional_id),
            cliente: Cliente::findOrFail($cita->cliente_id),
        );
    }
}
