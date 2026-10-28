<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Negocio;
use Illuminate\Contracts\View\View;

/**
 * Página mínima para T6: muestra servicios y abre el flujo de reserva.
 * La página armable por bloques (portada, equipo, galería...) es T8.
 */
final class PaginaNegocioController extends Controller
{
    public function mostrar(Negocio $negocio): View
    {
        return view('pagina-negocio', ['negocio' => $negocio]);
    }
}
