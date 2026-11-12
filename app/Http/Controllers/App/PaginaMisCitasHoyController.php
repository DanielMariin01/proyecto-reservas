<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

final class PaginaMisCitasHoyController extends Controller
{
    public function mostrar(Negocio $negocio): View
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        abort_if($usuario->profesional === null, 403);

        return view('panel-negocio.mis-citas-hoy', ['negocio' => $negocio]);
    }
}
