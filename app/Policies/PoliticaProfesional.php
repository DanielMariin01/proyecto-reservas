<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\Profesional;
use App\Models\Usuario;

final class PoliticaProfesional
{
    public function viewAny(Usuario $usuario): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    public function view(Usuario $usuario, Profesional $profesional): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    public function update(Usuario $usuario, Profesional $profesional): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    public function delete(Usuario $usuario, Profesional $profesional): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    private function puedeGestionarCatalogo(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarCatalogo->value);
    }
}
