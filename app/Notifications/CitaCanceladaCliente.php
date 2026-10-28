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

final class CitaCanceladaCliente extends Notification implements ShouldQueue
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
            ->subject("Tu cita en {$datos->negocio->nombre} quedó cancelada")
            ->greeting("Hola {$datos->cliente->nombre}")
            ->line("Tu cita de {$datos->servicio->nombre} del {$fecha} a las {$hora} quedó cancelada.")
            ->line('Si fue un error, puedes reservar de nuevo desde la página del negocio.');
    }
}
