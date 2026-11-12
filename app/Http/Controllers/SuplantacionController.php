<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SalirDeSuplantacion;
use Illuminate\Http\RedirectResponse;

final class SuplantacionController extends Controller
{
    public function salir(SalirDeSuplantacion $salirDeSuplantacion): RedirectResponse
    {
        $salirDeSuplantacion->ejecutar();

        return redirect()->route('admin.panel');
    }
}
