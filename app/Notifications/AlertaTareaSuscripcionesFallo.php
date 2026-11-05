<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AlertaTareaSuscripcionesFallo extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $mensajeError,
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
            ->subject('Falló la tarea diaria de suscripciones')
            ->line('La tarea suscripciones:actualizar-estados falló hoy y los negocios no se actualizaron.')
            ->line("Mensaje: {$this->mensajeError}");
    }
}
