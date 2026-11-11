<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;

final class PaginaNegocioController extends Controller
{
    public function index(): View
    {
        return view('admin.negocios');
    }

    public function create(): View
    {
        return view('admin.negocios-crear');
    }

    public function edit(Negocio $negocio): View
    {
        return view('admin.negocios-editar', ['negocio' => $negocio]);
    }

    public function show(Negocio $negocio): View
    {
        return view('admin.negocios-ver', ['negocio' => $negocio]);
    }
}
