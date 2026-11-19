<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Resources\Publico\RecursoProductoPublico;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductosController extends Controller
{
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $productos = Producto::query()
            ->where('negocio_id', $negocio->id)
            ->where('visible', true)
            ->with('categoriaProducto')
            ->orderBy('orden')
            ->get();

        return RecursoProductoPublico::collection($productos);
    }
}
