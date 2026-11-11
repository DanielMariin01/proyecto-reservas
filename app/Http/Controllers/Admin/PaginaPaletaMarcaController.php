<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaletaMarca;
use Illuminate\Contracts\View\View;

final class PaginaPaletaMarcaController extends Controller
{
    public function index(): View
    {
        return view('admin.paletas-marca');
    }

    public function create(): View
    {
        return view('admin.paletas-marca-crear');
    }

    public function edit(PaletaMarca $paleta): View
    {
        return view('admin.paletas-marca-editar', ['paleta' => $paleta]);
    }
}
