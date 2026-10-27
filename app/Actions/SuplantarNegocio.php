<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\NegocioSuplantado;
use App\Models\Negocio;
use App\Models\Usuario;

final class SuplantarNegocio
{
    public function ejecutar(Negocio $negocio, Usuario $superAdmin): void
    {
        session(['negocio_suplantado_id' => $negocio->id]);

        NegocioSuplantado::dispatch($negocio, $superAdmin);
    }
}
