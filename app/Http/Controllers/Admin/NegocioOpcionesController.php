<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;

/**
 * Lista mínima {id, nombre} para selects de filtro (hoy el de Registro de
 * errores por negocio). Deliberadamente fuera de cualquier Resource de
 * Filament: no depende de ningún recurso ya migrado ni por migrar.
 */
final class NegocioOpcionesController extends Controller
{
    public function mostrar(): JsonResponse
    {
        $negocios = Negocio::query()->orderBy('nombre')->get(['id', 'nombre']);

        return response()->json(['data' => $negocios]);
    }
}
