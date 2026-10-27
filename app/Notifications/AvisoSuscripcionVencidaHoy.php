<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Negocio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AvisoSuscripcionVencidaHoy extends Notification implements ShouldQueue
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
            ->subject("Tu suscripción de {$this->negocio->nombre} venció hoy")
            ->line("Tu negocio {$this->negocio->nombre} entró en periodo de gracia de {$this->negocio->dias_gracia} días.")
            ->line('Tu página pública y tu panel siguen funcionando normalmente durante este periodo.')
            ->line('Registra tu pago pronto para evitar que el negocio se suspenda.');
    }
}
