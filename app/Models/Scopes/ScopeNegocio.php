<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\Negocio;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtra por el negocio (tenant) activo en el panel app (RNF-01). Fuera de
 * ese contexto (panel admin, consola, pruebas) no filtra nada: el super
 * admin y las tareas internas necesitan ver todos los negocios.
 */
final class ScopeNegocio implements Scope
{
    public function apply(Builder $query, Model $model): void
    {
        $negocio = Filament::getTenant();

        if ($negocio instanceof Negocio) {
            $query->where($model->qualifyColumn('negocio_id'), $negocio->id);
        }
    }
}
