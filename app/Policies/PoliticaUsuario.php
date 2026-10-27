<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\Negocio;
use App\Models\Usuario;
use Filament\Facades\Filament;

final class PoliticaUsuario
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarUsuarios->value);
    }

    public function view(Usuario $usuario, Usuario $objetivo): bool
    {
        return $this->puedeGestionar($usuario, $objetivo);
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarUsuarios->value);
    }

    public function update(Usuario $usuario, Usuario $objetivo): bool
    {
        return $this->puedeGestionar($usuario, $objetivo);
    }

    public function delete(Usuario $usuario, Usuario $objetivo): bool
    {
        return $usuario->id !== $objetivo->id && $this->puedeGestionar($usuario, $objetivo);
    }

    private function puedeGestionar(Usuario $usuario, Usuario $objetivo): bool
    {
        if ($usuario->es_super_admin) {
            return true;
        }

        $negocioActual = Filament::getTenant();

        if (! $negocioActual instanceof Negocio) {
            return false;
        }

        return $usuario->can(Permiso::GestionarUsuarios->value)
            && $objetivo->negociosDondeTieneRol()->contains('id', $negocioActual->id);
    }
}
