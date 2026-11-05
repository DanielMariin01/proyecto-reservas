<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contratos\ServicioDeCorreo;
use App\DatosTransferencia\DatosDeCitaParaNotificacion;
use App\Enums\EstadoCitaValor;
use App\Events\CitaCambioDeEstado;
use App\Notifications\InvitacionADejarResena;
use Illuminate\Contracts\Queue\ShouldQueue;

final class EnviarCorreoDeInvitacionAResena implements ShouldQueue
{
    public function __construct(
        private readonly ServicioDeCorreo $servicioDeCorreo,
    ) {}

    public function handle(CitaCambioDeEstado $evento): void
    {
        if ($evento->cita->estado !== EstadoCitaValor::Completada) {
            return;
        }

        $datos = DatosDeCitaParaNotificacion::desde($evento->cita);

        if ($datos->cliente->correo === null) {
            return;
        }

        $this->servicioDeCorreo->enviarAlCorreo($datos->cliente->correo, new InvitacionADejarResena($evento->cita));
    }
}
