<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\CategoriaProducto;
use App\Models\Usuario;

final class PoliticaCategoriaProducto
{
    public function viewAny(Usuario $usuario): bool
    {
        return $this->puedeGestionarProductos($usuario);
    }

    public function view(Usuario $usuario, CategoriaProducto $categoriaProducto): bool
    {
        return $this->puedeGestionarProductos($usuario);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->puedeGestionarProductos($usuario);
    }

    public function update(Usuario $usuario, CategoriaProducto $categoriaProducto): bool
    {
        return $this->puedeGestionarProductos($usuario);
    }

    public function delete(Usuario $usuario, CategoriaProducto $categoriaProducto): bool
    {
        return $this->puedeGestionarProductos($usuario);
    }

    private function puedeGestionarProductos(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarCursosProductos->value);
    }
}
