<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class PaginaRegistroErrorController extends Controller
{
    public function mostrar(): View
    {
        return view('admin.registro-errores');
    }
}
