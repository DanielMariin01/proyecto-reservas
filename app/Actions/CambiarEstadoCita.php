<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EstadoCitaValor;
use App\Events\CitaCambioDeEstado;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Models\Cita;
use App\Models\Usuario;

final class CambiarEstadoCita
{
    public function ejecutar(Cita $cita, EstadoCitaValor $destino, ?Usuario $cambiadoPor = null): Cita
    {
        $estadoAnterior = $cita->estado;
        $estado = $cita->obtenerEstado();

        if (! $estado->puedeTransicionarA($destino)) {
            throw new TransicionDeEstadoInvalidaException($estado->valor(), $destino);
        }

        $cita->update(['estado' => $destino]);

        CitaCambioDeEstado::dispatch($cita, $estadoAnterior, $cambiadoPor);

        return $cita;
    }
}
