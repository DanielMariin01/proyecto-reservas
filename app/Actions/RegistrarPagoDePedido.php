<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EstadoPedidoValor;
use App\Enums\MetodoPago;
use App\Exceptions\PedidoNoPagadoException;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Usuario;

/**
 * Se ejecuta despues de CambiarEstadoPedido(Pagado) desde el controlador
 * (mismo orden que RegistrarPagoDeCita/completarConPago): el chequeo de
 * aqui es una verificacion defensiva de que ese paso ya ocurrio, no un
 * sustituto de la transicion de estado.
 */
final class RegistrarPagoDePedido
{
    public function ejecutar(Pedido $pedido, MetodoPago $metodo, int $valor, ?Usuario $registradoPor = null): Pago
    {
        if ($pedido->estado !== EstadoPedidoValor::Pagado) {
            throw new PedidoNoPagadoException;
        }

        return Pago::create([
            'negocio_id' => $pedido->negocio_id,
            'pedido_id' => $pedido->id,
            'metodo' => $metodo,
            'valor' => $valor,
            'propina' => 0,
            'registrado_por_id' => $registradoPor?->id,
        ]);
    }
}
