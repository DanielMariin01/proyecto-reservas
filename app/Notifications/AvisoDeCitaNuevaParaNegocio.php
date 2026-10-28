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

final class AvisoDeCitaNuevaParaNegocio extends Notification implements ShouldQueue
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
            ->subject("Nueva cita: {$datos->servicio->nombre} el {$fecha}")
            ->line("{$datos->cliente->nombre} reservó {$datos->servicio->nombre} con {$datos->profesional->nombre} para el {$fecha} a las {$hora}.")
            ->line("Celular del cliente: {$datos->cliente->celular}");
    }
}
