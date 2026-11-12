<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

final class PaginaPaginaWebController extends Controller
{
    public function mostrar(Negocio $negocio): View
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        if (! $usuario->es_super_admin) {
            abort_unless($usuario->can(Permiso::GestionarNegocio->value) && $negocio->permite_editar_pagina, 403);
        }

        return view('panel-negocio.pagina-web', ['negocio' => $negocio]);
    }
}
