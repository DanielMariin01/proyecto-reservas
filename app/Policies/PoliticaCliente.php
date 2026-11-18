<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Models\Negocio;
use App\Models\Usuario;

/**
 * RF-HCL-07: el panel `app` todavía no tiene el CRM completo (RF-CLI-01),
 * solo la lista y la ficha mínima que necesita Historia Clínica para
 * ubicar al cliente. Por eso solo hay `viewAny` — nada de
 * crear/editar/eliminar todavía. Puede verla quien administra clientes
 * (dueño/recepción, sin importar el tipo de negocio) o quien gestiona
 * historias clínicas (profesional) — pero a este último solo cuando el
 * negocio de verdad las usa (RF-HCL-01): si no, el permiso del rol no
 * tiene nada que hacer aquí y no debe abrir la lista de clientes.
 */
final class PoliticaCliente
{
    public function viewAny(Usuario $usuario): bool
    {
        if ($usuario->es_super_admin || $usuario->can(Permiso::GestionarClientes->value)) {
            return true;
        }

        $negocio = Negocio::actualOSinNegocio();

        return $negocio instanceof Negocio
            && $negocio->tipo->requiereHistoriaClinica()
            && $usuario->can(Permiso::GestionarHistoriasClinicas->value);
    }
}
