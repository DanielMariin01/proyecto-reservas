<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosNuevoGasto;
use App\Events\GastoRegistrado;
use App\Models\Gasto;
use App\Models\Usuario;

final class RegistrarGasto
{
    public function ejecutar(DatosNuevoGasto $datos, ?Usuario $registradoPor = null): Gasto
    {
        $gasto = Gasto::create([
            'negocio_id' => $datos->negocio->id,
            'monto' => $datos->monto,
            'descripcion' => $datos->descripcion,
            'fecha' => $datos->fecha->toDateString(),
            'registrado_por_id' => $registradoPor?->id,
        ]);

        GastoRegistrado::dispatch($gasto, $registradoPor);

        return $gasto;
    }
}
