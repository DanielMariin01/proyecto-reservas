<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosNuevoProfesional;
use App\Models\Profesional;
use App\Models\Usuario;

final class CrearProfesional
{
    public function __construct(
        private readonly VincularUsuarioAProfesional $vincularUsuarioAProfesional,
    ) {}

    public function ejecutar(DatosNuevoProfesional $datos, ?Usuario $creadoPor = null): Profesional
    {
        $profesional = Profesional::create([
            'negocio_id' => $datos->negocio->id,
            'nombre' => $datos->nombre,
            'foto' => $datos->foto,
            'especialidad' => $datos->especialidad,
            'biografia' => $datos->biografia,
            'certificaciones' => $datos->certificaciones,
            'color' => $datos->color,
        ]);

        if ($datos->nombreUsuario !== null && $datos->emailUsuario !== null) {
            $this->vincularUsuarioAProfesional->ejecutar(
                $profesional,
                $datos->nombreUsuario,
                $datos->emailUsuario,
                $creadoPor,
            );
        }

        return $profesional;
    }
}
