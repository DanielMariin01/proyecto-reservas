<?php

declare(strict_types=1);

namespace App\Contratos;

use Illuminate\Notifications\Notification;

/**
 * Adaptador sobre el servicio externo de correo (CLAUDE.md: "servicios
 * externos siempre detrás de una interfaz propia"). Hoy lo implementa el
 * sistema de notificaciones de Laravel, pero los listeners nunca llaman a
 * `Notification::send()` directamente: dependen de esta interfaz.
 */
interface ServicioDeCorreo
{
    /**
     * Para destinatarios con cuenta (Usuario), que ya implementan
     * Notifiable: un solo modelo o una Collection de ellos.
     */
    public function enviarA(object $destinatarios, Notification $notificacion): void;

    /**
     * Para destinatarios sin cuenta (Cliente), por correo suelto.
     */
    public function enviarAlCorreo(string $correo, Notification $notificacion): void;
}
