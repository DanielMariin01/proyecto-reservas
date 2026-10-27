<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Negocio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AvisoSuscripcionProximaAVencer extends Notification implements ShouldQueue
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
            ->subject("Tu suscripción de {$this->negocio->nombre} vence en 5 días")
            ->line("La próxima fecha de pago de {$this->negocio->nombre} es el {$this->negocio->proxima_fecha_pago?->format('d/m/Y')}.")
            ->line('Si no se registra el pago a tiempo, tu negocio entrará en un periodo de gracia y luego se suspenderá.');
    }
}
