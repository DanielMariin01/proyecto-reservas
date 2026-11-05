<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosNuevaResena;
use App\Enums\EstadoResenaValor;
use App\Events\ResenaCreada;
use App\Exceptions\ResenaNoElegibleException;
use App\Models\Resena;
use App\Resenas\CadenaReglasResena;

final class CrearResena
{
    public function __construct(
        private readonly CadenaReglasResena $cadenaReglas,
    ) {}

    public function ejecutar(DatosNuevaResena $datos): Resena
    {
        if (! $this->cadenaReglas->permite($datos->cita)) {
            throw new ResenaNoElegibleException;
        }

        $resena = Resena::create([
            'negocio_id' => $datos->cita->negocio_id,
            'cita_id' => $datos->cita->id,
            'cliente_id' => $datos->cita->cliente_id,
            'profesional_id' => $datos->cita->profesional_id,
            'calificacion' => $datos->calificacion,
            'comentario' => $datos->comentario,
            'estado' => EstadoResenaValor::Publicada,
        ]);

        ResenaCreada::dispatch($resena);

        return $resena;
    }
}
