<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Pedido;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Patrón Observador, igual que CitaCreada: un futuro correo de
 * confirmación de pedido se conecta aquí sin tocar CrearPedido.
 */
final class PedidoCreado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Pedido $pedido,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'pedido.creado',
            negocio: Negocio::find($this->pedido->negocio_id),
            modelo: Pedido::class,
            modeloId: $this->pedido->id,
            valoresDespues: [
                'estado' => $this->pedido->estado->value,
                'total' => $this->pedido->total,
            ],
        );
    }
}
