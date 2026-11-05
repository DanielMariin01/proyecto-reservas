<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\RegistroError;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AlertaErrorRepetido extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly RegistroError $registro,
        private readonly int $repeticiones,
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
            ->subject("Un error se repite: {$this->registro->clase_excepcion}")
            ->line("Ocurrió {$this->repeticiones} veces en la última hora en la ruta \"{$this->registro->ruta}\".")
            ->line("Mensaje: {$this->registro->mensaje}")
            ->line("Último código de referencia: {$this->registro->codigo_referencia}.");
    }
}
