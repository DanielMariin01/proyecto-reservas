<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\Usuario;

/**
 * Ver Finanzas es para quien ve reportes; registrar o eliminar un gasto
 * es trabajo de quien gestiona los pagos del negocio (dueño, recepción)
 * — igual de sensible que anular un pago (PoliticaPago::anular).
 */
final class PoliticaGasto
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::VerReportes->value);
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarPagos->value);
    }

    public function delete(Usuario $usuario): bool
    {
        return $usuario->es_super_admin || $usuario->can(Permiso::GestionarPagos->value);
    }
}
