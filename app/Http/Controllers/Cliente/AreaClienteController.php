<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class AreaClienteController extends Controller
{
    public function mostrar(): View
    {
        return view('mi-cuenta');
    }
}
