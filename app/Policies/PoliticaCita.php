<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\Cita;
use App\Models\Usuario;

final class PoliticaCita
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->es_super_admin
            || $usuario->can(Permiso::GestionarCitasTodas->value)
            || $usuario->can(Permiso::GestionarCitasPropias->value);
    }

    public function view(Usuario $usuario, Cita $cita): bool
    {
        return $this->puedeVerOGestionar($usuario, $cita);
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value);
    }

    public function update(Usuario $usuario, Cita $cita): bool
    {
        return $this->puedeVerOGestionar($usuario, $cita);
    }

    public function delete(Usuario $usuario, Cita $cita): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value);
    }

    private function puedeVerOGestionar(Usuario $usuario, Cita $cita): bool
    {
        if ($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value)) {
            return true;
        }

        if (! $usuario->can(Permiso::GestionarCitasPropias->value)) {
            return false;
        }

        return $usuario->profesional?->id === $cita->profesional_id;
    }
}
