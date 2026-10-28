<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DatosTransferencia\DatosDeCitaParaNotificacion;
use App\Models\Cita;
use App\Support\Dinero;
use App\Support\Fecha;
use App\Support\Hora;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ConfirmacionDeCitaCliente extends Notification implements ShouldQueue
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
        $enlace = url("/{$datos->negocio->slug}/reservas/{$this->cita->token_cancelacion}");

        return (new MailMessage)
            ->subject("Tu cita en {$datos->negocio->nombre} quedó reservada")
            ->greeting("Hola {$datos->cliente->nombre}")
            ->line("Tu cita de {$datos->servicio->nombre} con {$datos->profesional->nombre} quedó para el {$fecha} a las {$hora}.")
            ->line('Precio: '.Dinero::formatearPesos($this->cita->precio))
            ->action('Ver, cancelar o cambiar mi cita', $enlace)
            ->line('Si no reconoces esta reserva, puedes ignorar este correo.');
    }
}
