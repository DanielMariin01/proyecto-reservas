<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Enums\EstadoPedidoValor;
use App\Models\ItemPedido;
use App\Models\Negocio;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pedido
 */
final class RecursoPedido extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Pedido $pedido */
        $pedido = $this->resource;
        $zonaHoraria = Negocio::actual()->zona_horaria;

        return [
            'id' => $pedido->id,
            'creadoVisible' => $pedido->created_at?->setTimezone($zonaHoraria)->format('d/m/Y H:i'),
            'clienteNombre' => $pedido->cliente?->nombre,
            'clienteCelular' => $pedido->cliente?->celular,
            'estado' => $pedido->estado->value,
            'estadoEtiqueta' => $pedido->estado->etiqueta(),
            'tipoEntrega' => $pedido->tipo_entrega->value,
            'tipoEntregaEtiqueta' => $pedido->tipo_entrega->etiqueta(),
            'barrio' => $pedido->barrio,
            'nota' => $pedido->nota,
            'total' => $pedido->total,
            'items' => $pedido->items->map(fn (ItemPedido $item): array => [
                'nombreProducto' => $item->nombre_producto,
                'precioUnitario' => $item->precio_unitario,
                'cantidad' => $item->cantidad,
                'subtotal' => $item->subtotal,
            ]),
            'destinosCambioEstado' => $this->destinosCambioEstado($pedido),
            'puedeMarcarPagado' => $pedido->obtenerEstado()->puedeTransicionarA(EstadoPedidoValor::Pagado),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function destinosCambioEstado(Pedido $pedido): array
    {
        $estado = $pedido->obtenerEstado();

        return array_values(array_map(
            fn (EstadoPedidoValor $destino): array => ['value' => $destino->value, 'label' => $destino->etiqueta()],
            array_filter(
                EstadoPedidoValor::cases(),
                fn (EstadoPedidoValor $destino): bool => $destino !== EstadoPedidoValor::Pagado && $estado->puedeTransicionarA($destino),
            ),
        ));
    }
}
