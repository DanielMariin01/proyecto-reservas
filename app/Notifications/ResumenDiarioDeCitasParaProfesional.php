<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Support\Hora;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

final class ResumenDiarioDeCitasParaProfesional extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, Cita>  $citasDeHoy
     */
    public function __construct(
        private readonly Profesional $profesional,
        private readonly Collection $citasDeHoy,
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
        $zonaHoraria = Negocio::findOrFail($this->profesional->negocio_id)->zona_horaria;
        $mensaje = (new MailMessage)
            ->subject('Tus citas de hoy: '.$this->citasDeHoy->count());

        if ($this->citasDeHoy->isEmpty()) {
            return $mensaje->greeting("Hola {$this->profesional->nombre}")->line('Hoy no tienes citas agendadas.');
        }

        $mensaje->greeting("Hola {$this->profesional->nombre}");

        foreach ($this->citasDeHoy as $cita) {
            $hora = Hora::formatear($cita->inicio, $zonaHoraria);
            $servicio = Servicio::findOrFail($cita->servicio_id)->nombre;
            $cliente = Cliente::findOrFail($cita->cliente_id)->nombre;
            $mensaje->line("{$hora} — {$servicio} con {$cliente}");
        }

        return $mensaje;
    }
}
