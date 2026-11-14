<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EstadoPedidoValor;
use App\Events\PedidoCambioDeEstado;
use App\Exceptions\TransicionDeEstadoPedidoInvalidaException;
use App\Models\Pedido;
use App\Models\Usuario;

final class CambiarEstadoPedido
{
    public function ejecutar(Pedido $pedido, EstadoPedidoValor $destino, ?Usuario $cambiadoPor = null): Pedido
    {
        $estadoAnterior = $pedido->estado;
        $estado = $pedido->obtenerEstado();

        if (! $estado->puedeTransicionarA($destino)) {
            throw new TransicionDeEstadoPedidoInvalidaException($estado->valor(), $destino);
        }

        $pedido->update(['estado' => $destino]);

        PedidoCambioDeEstado::dispatch($pedido, $estadoAnterior, $cambiadoPor);

        return $pedido;
    }
}
