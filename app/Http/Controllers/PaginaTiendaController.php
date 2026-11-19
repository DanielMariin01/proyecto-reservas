<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class PaginaTiendaController extends Controller
{
    public function mostrar(Negocio $negocio): View
    {
        return view('tienda', ['negocio' => $negocio]);
    }
}
