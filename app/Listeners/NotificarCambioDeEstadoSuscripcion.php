<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\EstadoSuscripcion;
use App\Events\EstadoNegocioCambiado;
use App\Notifications\AvisoSuscripcionSuspendida;
use App\Notifications\AvisoSuscripcionVencidaHoy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

final class NotificarCambioDeEstadoSuscripcion implements ShouldQueue
{
    public function handle(EstadoNegocioCambiado $evento): void
    {
        $notificacion = match ($evento->estadoNuevo) {
            EstadoSuscripcion::Vencido => new AvisoSuscripcionVencidaHoy($evento->negocio),
            EstadoSuscripcion::Suspendido => new AvisoSuscripcionSuspendida($evento->negocio),
            default => null,
        };

        if ($notificacion === null) {
            return;
        }

        Notification::send($evento->negocio->duenos(), $notificacion);
    }
}
