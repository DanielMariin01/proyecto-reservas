<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Spatie\Permission\Models\Role;

final class PaginaUsuarioController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $this->authorize('viewAny', Usuario::class);

        return view('panel-negocio.usuarios', ['negocio' => $negocio]);
    }

    public function create(Negocio $negocio): View
    {
        $this->authorize('create', Usuario::class);

        return view('panel-negocio.usuarios-crear', ['negocio' => $negocio]);
    }

    public function edit(Negocio $negocio, int $usuario): View
    {
        $usuarioModelo = Usuario::query()->findOrFail($usuario);
        $this->authorize('update', $usuarioModelo);

        /** @var Role|null $rol */
        $rol = $usuarioModelo->roles->first();

        return view('panel-negocio.usuarios-editar', [
            'negocio' => $negocio,
            'usuario' => $usuarioModelo,
            'rol' => $rol?->name,
        ]);
    }
}
