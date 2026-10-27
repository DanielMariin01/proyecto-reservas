<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Negocio;
use App\Models\Usuario;

final class PoliticaNegocio
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->es_super_admin;
    }

    public function view(Usuario $usuario, Negocio $negocio): bool
    {
        return $usuario->es_super_admin;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->es_super_admin;
    }

    public function update(Usuario $usuario, Negocio $negocio): bool
    {
        return $usuario->es_super_admin;
    }

    public function delete(Usuario $usuario, Negocio $negocio): bool
    {
        return false;
    }
}
