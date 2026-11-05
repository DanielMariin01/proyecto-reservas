<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Usuario;

/**
 * CU-08 / F5.2: registrar un pago lo hace quien gestiona todas las citas
 * (dueño, recepción) o el propio profesional sobre su propia cita. Anular
 * un pago ya registrado es trabajo de cierre de caja del dueño/recepción
 * únicamente (F5.2: "corrige pagos mal registrados"), nunca del
 * profesional sobre sí mismo.
 */
final class PoliticaPago
{
    public function create(Usuario $usuario, Cita $cita): bool
    {
        if ($usuario->es_super_admin || $usuario->can(Permiso::GestionarPagos->value)) {
            return true;
        }

        if (! $usuario->can(Permiso::GestionarCitasPropias->value)) {
            return false;
        }

        return $usuario->profesional?->id === $cita->profesional_id;
    }

    public function anular(Usuario $usuario, Pago $pago): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarPagos->value);
    }
}
