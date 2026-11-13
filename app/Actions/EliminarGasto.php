<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\GastoEliminado;
use App\Models\Gasto;
use App\Models\Usuario;

final class EliminarGasto
{
    public function ejecutar(Gasto $gasto, ?Usuario $eliminadoPor = null): void
    {
        $gastoId = $gasto->id;
        $negocioId = $gasto->negocio_id;
        $monto = $gasto->monto;
        $descripcion = $gasto->descripcion;

        $gasto->delete();

        GastoEliminado::dispatch($gastoId, $negocioId, $monto, $descripcion, $eliminadoPor);
    }
}
