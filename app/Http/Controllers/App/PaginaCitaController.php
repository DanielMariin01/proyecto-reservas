<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

final class PaginaCitaController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', Cita::class);

        return view('panel-negocio.citas', ['negocio' => $negocio]);
    }

    public function create(Negocio $negocio): View
    {
        $this->authorize('create', Cita::class);

        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();
        $puedeForzar = $usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value);

        return view('panel-negocio.citas-crear', ['negocio' => $negocio, 'puedeForzar' => $puedeForzar]);
    }
}
