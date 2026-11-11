<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;

final class PaginaUsuarioController extends Controller
{
    public function index(): View
    {
        return view('admin.usuarios');
    }

    public function create(): View
    {
        return view('admin.usuarios-crear');
    }

    public function edit(Usuario $usuario): View
    {
        return view('admin.usuarios-editar', ['usuario' => $usuario]);
    }
}
