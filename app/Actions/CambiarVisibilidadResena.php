<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EstadoResenaValor;
use App\Events\ResenaCambioDeVisibilidad;
use App\Models\Resena;
use App\Models\Usuario;
use Carbon\CarbonImmutable;

final class CambiarVisibilidadResena
{
    public function ejecutar(Resena $resena, EstadoResenaValor $destino, Usuario $cambiadoPor): Resena
    {
        $estadoAnterior = $resena->estado;

        $resena->update([
            'estado' => $destino,
            'oculta_en' => $destino === EstadoResenaValor::Oculta ? CarbonImmutable::now() : null,
            'oculta_por_id' => $destino === EstadoResenaValor::Oculta ? $cambiadoPor->id : null,
        ]);

        ResenaCambioDeVisibilidad::dispatch($resena, $estadoAnterior, $cambiadoPor);

        return $resena;
    }
}
