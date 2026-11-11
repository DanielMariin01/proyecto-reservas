<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

final class PaginaPanelDelDiaController extends Controller
{
    public function mostrar(Negocio $negocio): View
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::guard('web')->user();

        abort_if($usuario === null, 403);
        abort_unless($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value), 403);

        return view('panel-negocio.panel-del-dia', ['negocio' => $negocio]);
    }
}
