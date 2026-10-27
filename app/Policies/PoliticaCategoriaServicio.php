<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\CategoriaServicio;
use App\Models\Usuario;

final class PoliticaCategoriaServicio
{
    public function viewAny(Usuario $usuario): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    public function view(Usuario $usuario, CategoriaServicio $categoriaServicio): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    public function update(Usuario $usuario, CategoriaServicio $categoriaServicio): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    public function delete(Usuario $usuario, CategoriaServicio $categoriaServicio): bool
    {
        return $this->puedeGestionarCatalogo($usuario);
    }

    private function puedeGestionarCatalogo(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarCatalogo->value);
    }
}
