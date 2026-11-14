<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Pedido;
use Illuminate\Contracts\View\View;

final class PaginaPedidoController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', Pedido::class);

        return view('panel-negocio.pedidos', ['negocio' => $negocio]);
    }
}
