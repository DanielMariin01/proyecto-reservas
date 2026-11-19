<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Gasto;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class PaginaFinanzasController extends Controller
{
    public function mostrar(Negocio $negocio): View
    {
        $this->authorize('viewAny', Gasto::class);

        return view('panel-negocio.finanzas', ['negocio' => $negocio]);
    }
}
