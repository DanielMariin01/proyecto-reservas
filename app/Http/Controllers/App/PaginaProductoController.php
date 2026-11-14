<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Contracts\View\View;

final class PaginaProductoController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', Producto::class);

        return view('panel-negocio.productos', ['negocio' => $negocio]);
    }

    public function create(Negocio $negocio): View
    {
        $this->authorize('create', Producto::class);

        return view('panel-negocio.productos-crear', ['negocio' => $negocio]);
    }

    public function edit(Negocio $negocio, int $producto): View
    {
        $productoModelo = Producto::query()->findOrFail($producto);
        $this->authorize('update', $productoModelo);

        return view('panel-negocio.productos-editar', ['negocio' => $negocio, 'producto' => $productoModelo]);
    }
}
