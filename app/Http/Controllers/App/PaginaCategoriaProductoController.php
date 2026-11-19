<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class PaginaCategoriaProductoController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', CategoriaProducto::class);

        return view('panel-negocio.categorias-producto', ['negocio' => $negocio]);
    }

    public function create(Negocio $negocio): View
    {
        $this->authorize('create', CategoriaProducto::class);

        return view('panel-negocio.categorias-producto-crear', ['negocio' => $negocio]);
    }

    public function edit(Negocio $negocio, int $categoriaProducto): View
    {
        $categoria = CategoriaProducto::query()->findOrFail($categoriaProducto);
        $this->authorize('update', $categoria);

        return view('panel-negocio.categorias-producto-editar', ['negocio' => $negocio, 'categoriaProducto' => $categoria]);
    }
}
