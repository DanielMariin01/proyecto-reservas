<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\Resena;
use App\Models\Usuario;

final class PoliticaResena
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->es_super_admin
            || $usuario->can(Permiso::GestionarResenas->value)
            || $usuario->profesional !== null;
    }

    public function view(Usuario $usuario, Resena $resena): bool
    {
        return $this->puedeVerOGestionar($usuario, $resena);
    }

    public function update(Usuario $usuario, Resena $resena): bool
    {
        return $this->puedeVerOGestionar($usuario, $resena);
    }

    private function puedeVerOGestionar(Usuario $usuario, Resena $resena): bool
    {
        if ($usuario->es_super_admin || $usuario->can(Permiso::GestionarResenas->value)) {
            return true;
        }

        return $usuario->profesional?->id === $resena->profesional_id;
    }
}
