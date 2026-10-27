<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Negocio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AvisoSuscripcionSuspendida extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Negocio $negocio,
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
        return (new MailMessage)
            ->subject("Tu negocio {$this->negocio->nombre} fue suspendido")
            ->line('Tu página pública ya no recibe reservas y nadie puede entrar al panel.')
            ->line('Comunícate con soporte para ponerte al día con tu suscripción y reactivarlo.');
    }
}
