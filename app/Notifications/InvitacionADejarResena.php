<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DatosTransferencia\DatosDeCitaParaNotificacion;
use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class InvitacionADejarResena extends Notification implements ShouldQueue
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
        $enlace = url("/{$datos->negocio->slug}/reservas/{$this->cita->token_cancelacion}");

        return (new MailMessage)
            ->subject("¿Cómo te fue en {$datos->negocio->nombre}?")
            ->greeting("Hola {$datos->cliente->nombre}")
            ->line("Esperamos que hayas quedado feliz con tu {$datos->servicio->nombre} con {$datos->profesional->nombre}.")
            ->line('Cuéntanos cómo te fue: tu opinión ayuda a otros clientes a elegir con confianza.')
            ->action('Dejar mi reseña', $enlace)
            ->line('Solo toma un minuto.');
    }
}
