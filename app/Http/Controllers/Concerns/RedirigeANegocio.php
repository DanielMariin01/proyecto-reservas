<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Negocio;
use Symfony\Component\HttpFoundation\Response;

trait RedirigeANegocio
{
    private function redirigirANegocio(Negocio $negocio): Response
    {
        if (! $negocio->estaOperativo()) {
            return response()->view('negocio-suspendido', ['negocio' => $negocio], 403);
        }

        return redirect()->route('app.panel', $negocio);
    }
}
