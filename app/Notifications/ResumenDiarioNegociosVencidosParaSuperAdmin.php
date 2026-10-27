<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Negocio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResumenDiarioNegociosVencidosParaSuperAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, Negocio>  $negociosVencidos
     */
    public function __construct(
        private readonly Collection $negociosVencidos,
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
        $mensaje = (new MailMessage)
            ->subject('Negocios vencidos hoy: '.$this->negociosVencidos->count());

        if ($this->negociosVencidos->isEmpty()) {
            return $mensaje->line('Ningún negocio está en periodo de gracia hoy.');
        }

        foreach ($this->negociosVencidos as $negocio) {
            $mensaje->line("{$negocio->nombre} — vencido desde {$negocio->vencido_desde?->format('d/m/Y')}");
        }

        return $mensaje;
    }
}
