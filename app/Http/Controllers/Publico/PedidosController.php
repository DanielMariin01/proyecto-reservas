<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Actions\CrearPedido;
use App\DatosTransferencia\DatosItemPedido;
use App\DatosTransferencia\DatosPedido;
use App\Enums\TipoEntregaValor;
use App\Exceptions\CarritoVacioException;
use App\Exceptions\EntregaInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publico\SolicitudPedidoPublico;
use App\Http\Resources\Publico\RecursoPedidoPublico;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;

final class PedidosController extends Controller
{
    public function __construct(
        private readonly CrearPedido $crearPedido,
    ) {}

    public function store(SolicitudPedidoPublico $request, Negocio $negocio): JsonResponse
    {
        $items = $this->resolverItems($request->validated()['items'], $negocio);

        try {
            $pedido = $this->crearPedido->ejecutar(new DatosPedido(
                negocio: $negocio,
                items: $items,
                nombreCliente: (string) $request->string('nombre'),
                celularCliente: (string) $request->string('celular'),
                correoCliente: $request->filled('correo') ? (string) $request->string('correo') : null,
                tipoEntrega: TipoEntregaValor::from((string) $request->string('tipo_entrega')),
                barrio: $request->filled('barrio') ? (string) $request->string('barrio') : null,
                nota: $request->filled('nota') ? (string) $request->string('nota') : null,
            ));
        } catch (CarritoVacioException|EntregaInvalidaException $excepcion) {
            return response()->json(['mensaje' => $excepcion->getMessage()], 422);
        }

        return (new RecursoPedidoPublico($pedido->load('items')))->response()->setStatusCode(201);
    }

    /**
     * @param  list<array{producto_id: int, cantidad: int}>  $items
     * @return list<DatosItemPedido>
     */
    private function resolverItems(array $items, Negocio $negocio): array
    {
        return array_map(
            fn (array $item): DatosItemPedido => new DatosItemPedido(
                producto: Producto::query()->where('negocio_id', $negocio->id)->findOrFail($item['producto_id']),
                cantidad: (int) $item['cantidad'],
            ),
            $items,
        );
    }
}
