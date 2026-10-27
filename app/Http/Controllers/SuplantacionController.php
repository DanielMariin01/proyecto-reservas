<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SalirDeSuplantacion;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

final class SuplantacionController extends Controller
{
    public function salir(SalirDeSuplantacion $salirDeSuplantacion): RedirectResponse
    {
        $salirDeSuplantacion->ejecutar();

        $url = Filament::getPanel('admin')->getUrl();

        abort_if($url === null, 500, 'No se pudo generar la URL del panel admin.');

        return redirect()->to($url);
    }
}
