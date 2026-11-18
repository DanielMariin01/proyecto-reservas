<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EstadoPedidoValor;
use App\Enums\MetodoPago;
use App\Exceptions\MontoDePagoIncorrectoException;
use App\Exceptions\PedidoNoPagadoException;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Usuario;

/**
 * Se ejecuta despues de CambiarEstadoPedido(Pagado) desde el controlador
 * (mismo orden que RegistrarPagoDeCita/completarConPago): el chequeo de
 * aqui es una verificacion defensiva de que ese paso ya ocurrio, no un
 * sustituto de la transicion de estado.
 *
 * El monto pagado debe coincidir exactamente con el total del pedido:
 * no se permite marcarlo "Pagado" por menos (ni por mas) de lo que
 * cuesta. Igual que en RegistrarPagoDeCita, validar() se expone aparte
 * de ejecutar() para que el controlador compruebe el monto *antes* de
 * llamar a CambiarEstadoPedido(Pagado) - Pagado no vuelve a Pendiente
 * (EstadoPagado::puedeTransicionarA() solo permite Entregado o
 * Cancelado), asi que un rechazo despues del cambio de estado dejaria
 * el pedido marcado pagado sin ningun pago real y sin forma de
 * reintentarlo.
 */
final class RegistrarPagoDePedido
{
    public function validar(Pedido $pedido, int $valor): void
    {
        if ($valor !== $pedido->total) {
            throw new MontoDePagoIncorrectoException($pedido->total, $valor);
        }
    }

    public function ejecutar(Pedido $pedido, MetodoPago $metodo, int $valor, ?Usuario $registradoPor = null): Pago
    {
        if ($pedido->estado !== EstadoPedidoValor::Pagado) {
            throw new PedidoNoPagadoException;
        }

        $this->validar($pedido, $valor);

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
