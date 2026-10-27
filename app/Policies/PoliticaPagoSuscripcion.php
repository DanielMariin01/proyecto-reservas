<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PagoSuscripcion;
use App\Models\Usuario;

final class PoliticaPagoSuscripcion
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->es_super_admin;
    }

    public function view(Usuario $usuario, PagoSuscripcion $pago): bool
    {
        return $usuario->es_super_admin;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->es_super_admin;
    }
}
