<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Resources\Publico\RecursoProfesionalPublico;
use App\Models\Negocio;
use App\Models\Profesional;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProfesionalesController extends Controller
{
    public function index(Negocio $negocio): AnonymousResourceCollection
    {
        $profesionales = Profesional::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->with('servicios')
            ->get();

        return RecursoProfesionalPublico::collection($profesionales);
    }
}
