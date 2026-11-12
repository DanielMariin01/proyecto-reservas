<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioDisponibilidad;
use App\Support\Hora;
use Carbon\CarbonImmutable;

/**
 * RF-DIS-05: al guardar un horario, el dueño o el profesional deben poder
 * ver las franjas que vería un cliente para un servicio de ejemplo, sin
 * tener que abrir la página pública. Reutilizable desde cualquier pantalla
 * que tenga un Profesional en contexto (vista del dueño o autoservicio).
 */
final class ObtenerVistaPreviaDisponibilidad
{
    public function __construct(private readonly ServicioDisponibilidad $servicioDisponibilidad) {}

    /**
     * @return list<string>
     */
    public function ejecutar(Profesional $profesional, Servicio $servicio, CarbonImmutable $fecha): array
    {
        $negocio = Negocio::findOrFail($profesional->negocio_id);

        return array_values(
            $this->servicioDisponibilidad
                ->obtenerFranjasDisponibles($profesional, $servicio, $fecha)
                ->map(fn ($hora): string => Hora::formatear($hora->inicio, $negocio->zona_horaria))
                ->all(),
        );
    }
}
