<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\App\RecursoPedido;
use App\Models\Negocio;
use App\Models\Pedido;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PedidoController extends Controller
{
    /**
     * El parametro Negocio $negocio no se usa directamente, pero es
     * necesario: Laravel solo resuelve el binding implicito de
     * {negocio:slug} cuando el metodo del controlador lo tipa, y
     * ResolverNegocioDesdeRuta depende de que ese binding ya este
     * resuelto para fijar el contexto del negocio actual.
     */
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Pedido::class);

        $pedidos = Pedido::query()->with(['cliente', 'items'])->latest()->get();

        return RecursoPedido::collection($pedidos);
    }
}
