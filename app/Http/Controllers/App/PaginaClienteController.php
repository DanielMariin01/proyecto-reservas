<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class PaginaClienteController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', Cliente::class);

        return view('panel-negocio.clientes', ['negocio' => $negocio]);
    }

    public function show(Negocio $negocio, int $cliente): View
    {
        $this->authorize('viewAny', Cliente::class);

        $clienteModelo = Cliente::query()->findOrFail($cliente);

        return view('panel-negocio.clientes-ver', ['negocio' => $negocio, 'cliente' => $clienteModelo]);
    }
}
