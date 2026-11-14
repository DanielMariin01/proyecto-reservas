<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\Models\ItemPedido;
use App\Models\Negocio;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pedido
 */
final class RecursoPedidoPublico extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Negocio $negocio */
        $negocio = $request->route('negocio');
        /** @var Pedido $pedido */
        $pedido = $this->resource;

        return [
            'id' => $pedido->id,
            'items' => $pedido->items->map(fn (ItemPedido $item): array => [
                'nombreProducto' => $item->nombre_producto,
                'precioUnitario' => $item->precio_unitario,
                'cantidad' => $item->cantidad,
                'subtotal' => $item->subtotal,
            ]),
            'total' => $pedido->total,
            'tipoEntrega' => $pedido->tipo_entrega->value,
            'tipoEntregaEtiqueta' => $pedido->tipo_entrega->etiqueta(),
            'barrio' => $pedido->barrio,
            'estado' => $pedido->estado->value,
            'direccion' => $negocio->direccion,
            'whatsapp' => $negocio->whatsapp,
            'nombreNegocio' => $negocio->nombre,
        ];
    }
}
