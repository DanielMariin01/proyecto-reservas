<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DatosTransferencia\DatosDeCitaParaNotificacion;
use App\Models\Cita;
use App\Support\Fecha;
use App\Support\Hora;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AvisoDeCitaCanceladaParaNegocio extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Cita $cita,
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
        $fecha = Fecha::formatear($this->cita->inicio, $datos->negocio->zona_horaria);
        $hora = Hora::formatear($this->cita->inicio, $datos->negocio->zona_horaria);

        return (new MailMessage)
            ->subject("Cita cancelada: {$datos->servicio->nombre} el {$fecha}")
            ->line("{$datos->cliente->nombre} canceló la cita de {$datos->servicio->nombre} con {$datos->profesional->nombre} del {$fecha} a las {$hora}.")
            ->when($this->cita->motivo_cancelacion !== null, fn (MailMessage $mensaje): MailMessage => $mensaje->line("Motivo: {$this->cita->motivo_cancelacion}"))
            ->line('Esa franja ya quedó libre en la agenda.');
    }
}
