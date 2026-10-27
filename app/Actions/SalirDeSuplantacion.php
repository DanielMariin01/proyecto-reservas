<?php

declare(strict_types=1);

namespace App\Actions;

final class SalirDeSuplantacion
{
    public function ejecutar(): void
    {
        session()->forget('negocio_suplantado_id');
    }
}
