<?php

declare(strict_types=1);

namespace App\Services;

use App\Contratos\ServicioDeCorreo;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as FachadaNotificacion;

final class ServicioDeCorreoLaravel implements ServicioDeCorreo
{
    public function enviarA(object $destinatarios, Notification $notificacion): void
    {
        FachadaNotificacion::send($destinatarios, $notificacion);
    }

    public function enviarAlCorreo(string $correo, Notification $notificacion): void
    {
        FachadaNotificacion::route('mail', $correo)->notify($notificacion);
    }
}
