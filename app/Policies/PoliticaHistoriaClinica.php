<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permiso;
use App\Enums\RolNegocio;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Usuario;

/**
 * RF-HCL-04: dato de salud sensible. El dueño ve y gestiona todas las
 * historias del negocio; el profesional solo las que él mismo atendió
 * (mismo esquema que PoliticaCita::puedeVerOGestionar); recepción no
 * tiene acceso, aunque sí administre clientes.
 */
final class PoliticaHistoriaClinica
{
    public function viewAny(Usuario $usuario): bool
    {
        return $this->negocioRequiereHistoriaClinica()
            && ($usuario->es_super_admin || $usuario->can(Permiso::GestionarHistoriasClinicas->value));
    }

    public function view(Usuario $usuario, HistoriaClinica $historiaClinica): bool
    {
        return $this->puedeVerOGestionar($usuario, $historiaClinica);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->viewAny($usuario);
    }

    /**
     * Para el listado (RF-HCL-04): el dueño ve todas las historias del
     * cliente, el profesional solo debe ver las suyas en la lista, no
     * solo al intentar abrirlas (`view`/`update`/`delete` ya lo impiden,
     * pero la lista no debe insinuar fecha ni quién atendió de un
     * registro ajeno). Devuelve `null` cuando no hay que filtrar.
     */
    public function profesionalIdParaFiltrarListado(Usuario $usuario): ?int
    {
        if ($usuario->es_super_admin || $usuario->hasRole(RolNegocio::Dueno->value)) {
            return null;
        }

        return $usuario->profesional?->id;
    }

    public function update(Usuario $usuario, HistoriaClinica $historiaClinica): bool
    {
        return $this->puedeVerOGestionar($usuario, $historiaClinica);
    }

    public function delete(Usuario $usuario, HistoriaClinica $historiaClinica): bool
    {
        return $this->puedeVerOGestionar($usuario, $historiaClinica);
    }

    private function puedeVerOGestionar(Usuario $usuario, HistoriaClinica $historiaClinica): bool
    {
        if (! $this->negocioRequiereHistoriaClinica()) {
            return false;
        }

        if ($usuario->es_super_admin) {
            return true;
        }

        if (! $usuario->can(Permiso::GestionarHistoriasClinicas->value)) {
            return false;
        }

        if ($usuario->hasRole(RolNegocio::Dueno->value)) {
            return true;
        }

        return $usuario->profesional?->id === $historiaClinica->profesional_id;
    }

    private function negocioRequiereHistoriaClinica(): bool
    {
        $negocio = Negocio::actualOSinNegocio();

        return $negocio instanceof Negocio && $negocio->tipo->requiereHistoriaClinica();
    }
}
