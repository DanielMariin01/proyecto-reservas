<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Negocio;
use Filament\Facades\Filament;
use Symfony\Component\HttpFoundation\Response;

trait RedirigeANegocio
{
    private function redirigirANegocio(Negocio $negocio): Response
    {
        if (! $negocio->estaOperativo()) {
            return response()->view('negocio-suspendido', ['negocio' => $negocio], 403);
        }

        $url = Filament::getPanel('app')->getUrl($negocio);

        abort_if($url === null, 500, 'No se pudo generar la URL del panel del negocio.');

        return redirect()->to($url);
    }
}
