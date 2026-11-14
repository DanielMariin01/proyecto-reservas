<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\Pedido;
use App\Models\Usuario;

final class PoliticaPedido
{
    public function viewAny(Usuario $usuario): bool
    {
        return $this->puedeGestionarPedidos($usuario);
    }

    public function view(Usuario $usuario, Pedido $pedido): bool
    {
        return $this->puedeGestionarPedidos($usuario);
    }

    public function update(Usuario $usuario, Pedido $pedido): bool
    {
        return $this->puedeGestionarPedidos($usuario);
    }

    private function puedeGestionarPedidos(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarCursosProductos->value);
    }
}
