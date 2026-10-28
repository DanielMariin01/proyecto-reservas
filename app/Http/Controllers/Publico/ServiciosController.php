<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Resources\Publico\RecursoServicioPublico;
use App\Models\Negocio;
use App\Models\Servicio;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ServiciosController extends Controller
{
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $servicios = Servicio::query()
            ->where('negocio_id', $negocio->id)
            ->where('visible', true)
            ->with('categoriaServicio')
            ->orderBy('orden')
            ->get();

        return RecursoServicioPublico::collection($servicios);
    }
}
