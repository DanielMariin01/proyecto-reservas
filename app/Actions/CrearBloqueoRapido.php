<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosBloqueoRapido;
use App\Enums\TipoBloqueo;
use App\Models\Bloqueo;
use App\Models\Negocio;
use Carbon\CarbonImmutable;

/**
 * RF-DIS-03: "no atiendo hoy de 3 a 5". Recibe la fecha y las horas tal
 * como las escribe el profesional (hora de pared del negocio) y las
 * convierte a UTC antes de guardar.
 */
final class CrearBloqueoRapido
{
    public function ejecutar(DatosBloqueoRapido $datos): Bloqueo
    {
        $negocio = Negocio::findOrFail($datos->profesional->negocio_id);
        $fechaTexto = $datos->fecha->format('Y-m-d');

        return Bloqueo::create([
            'negocio_id' => $negocio->id,
            'profesional_id' => $datos->profesional->id,
            'tipo' => TipoBloqueo::Bloqueo,
            'inicio' => CarbonImmutable::parse("{$fechaTexto} {$datos->horaInicio}", $negocio->zona_horaria)->utc(),
            'fin' => CarbonImmutable::parse("{$fechaTexto} {$datos->horaFin}", $negocio->zona_horaria)->utc(),
            'motivo' => $datos->motivo,
        ]);
    }
}
