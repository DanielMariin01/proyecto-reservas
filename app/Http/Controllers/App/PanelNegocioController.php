<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class PanelNegocioController extends Controller
{
    public function mostrar(Negocio $negocio): View
    {
        return view('panel-negocio', ['negocio' => $negocio]);
    }
}
