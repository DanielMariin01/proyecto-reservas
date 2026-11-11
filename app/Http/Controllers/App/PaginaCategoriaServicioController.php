<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CategoriaServicio;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class PaginaCategoriaServicioController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', CategoriaServicio::class);

        return view('panel-negocio.categorias-servicio', ['negocio' => $negocio]);
    }

    public function create(Negocio $negocio): View
    {
        $this->authorize('create', CategoriaServicio::class);

        return view('panel-negocio.categorias-servicio-crear', ['negocio' => $negocio]);
    }

    public function edit(Negocio $negocio, int $categoriaServicio): View
    {
        $categoria = CategoriaServicio::query()->findOrFail($categoriaServicio);
        $this->authorize('update', $categoria);

        return view('panel-negocio.categorias-servicio-editar', ['negocio' => $negocio, 'categoriaServicio' => $categoria]);
    }
}
