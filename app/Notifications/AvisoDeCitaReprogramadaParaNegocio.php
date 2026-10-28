<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DatosTransferencia\DatosDeCitaParaNotificacion;
use App\Models\Cita;
use App\Support\Fecha;
use App\Support\Hora;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AvisoDeCitaReprogramadaParaNegocio extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Cita $cita,
        private readonly CarbonImmutable $inicioAnterior,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $datos = DatosDeCitaParaNotificacion::desde($this->cita);
        $fechaAnterior = Fecha::formatear($this->inicioAnterior, $datos->negocio->zona_horaria);
        $horaAnterior = Hora::formatear($this->inicioAnterior, $datos->negocio->zona_horaria);
        $fechaNueva = Fecha::formatear($this->cita->inicio, $datos->negocio->zona_horaria);
        $horaNueva = Hora::formatear($this->cita->inicio, $datos->negocio->zona_horaria);

        return (new MailMessage)
            ->subject("Cita reprogramada: {$datos->servicio->nombre}")
            ->line("{$datos->cliente->nombre} cambió su cita de {$datos->servicio->nombre} con {$datos->profesional->nombre}.")
            ->line("Antes: {$fechaAnterior} a las {$horaAnterior}.")
            ->line("Ahora: {$fechaNueva} a las {$horaNueva}.");
    }
}
