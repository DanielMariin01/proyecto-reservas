<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AlertaColaDetenida extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $minutosDetenida,
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
            ->subject('La cola de trabajos dejó de procesarse')
            ->line("Hay un trabajo pendiente desde hace {$this->minutosDetenida} minutos sin procesarse.")
            ->line('Revisa que el cron del hosting siga ejecutando el scheduler cada minuto.');
    }
}
