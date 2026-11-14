<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosItemPedido;
use App\DatosTransferencia\DatosPedido;
use App\Enums\EstadoPedidoValor;
use App\Enums\TipoEntregaValor;
use App\Events\PedidoCreado;
use App\Exceptions\CarritoVacioException;
use App\Exceptions\EntregaInvalidaException;
use App\Models\ItemPedido;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

final class CrearPedido
{
    public function __construct(
        private readonly ResolverOCrearCliente $resolverOCrearCliente,
    ) {}

    public function ejecutar(DatosPedido $datos): Pedido
    {
        $this->validarEntrega($datos);

        $cliente = $this->resolverOCrearCliente->ejecutar(
            $datos->negocio,
            $datos->nombreCliente,
            $datos->celularCliente,
            $datos->correoCliente,
        );

        $pedido = DB::transaction(function () use ($datos, $cliente): Pedido {
            $pedido = Pedido::create([
                'negocio_id' => $datos->negocio->id,
                'cliente_id' => $cliente->id,
                'estado' => EstadoPedidoValor::Pendiente,
                'tipo_entrega' => $datos->tipoEntrega,
                'barrio' => $datos->barrio,
                'nota' => $datos->nota,
                'total' => $this->calcularTotal($datos),
            ]);

            foreach ($datos->items as $item) {
                ItemPedido::create([
                    'negocio_id' => $datos->negocio->id,
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item->producto->id,
                    'nombre_producto' => $item->producto->nombre,
                    'precio_unitario' => $item->producto->precio,
                    'cantidad' => $item->cantidad,
                    'subtotal' => $item->producto->precio * $item->cantidad,
                ]);
            }

            return $pedido;
        });

        PedidoCreado::dispatch($pedido);

        return $pedido;
    }

    private function validarEntrega(DatosPedido $datos): void
    {
        if ($datos->items === []) {
            throw new CarritoVacioException;
        }

        if ($datos->tipoEntrega === TipoEntregaValor::Domicilio && ! filled($datos->barrio)) {
            throw new EntregaInvalidaException;
        }
    }

    private function calcularTotal(DatosPedido $datos): int
    {
        return array_sum(array_map(
            fn (DatosItemPedido $item): int => $item->producto->precio * $item->cantidad,
            $datos->items,
        ));
    }
}
