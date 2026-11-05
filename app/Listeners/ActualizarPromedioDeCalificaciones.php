<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ResenaCambioDeVisibilidad;
use App\Events\ResenaCreada;
use App\Services\ServicioResenas;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Patrón Observador: ni CrearResena ni CambiarVisibilidadResena saben que
 * existen promedios cacheados en Negocio y Profesional — este listener los
 * mantiene al día escuchando los eventos, igual que EnviarCorreosDeCitaCreada
 * hace con el correo de confirmación.
 */
final class ActualizarPromedioDeCalificaciones implements ShouldQueue
{
    public function __construct(
        private readonly ServicioResenas $servicioResenas,
    ) {}

    public function handle(ResenaCreada|ResenaCambioDeVisibilidad $evento): void
    {
        $this->servicioResenas->recalcularPromedios($evento->resena);
    }
}
