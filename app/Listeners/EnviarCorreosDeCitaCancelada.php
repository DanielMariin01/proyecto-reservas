<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contratos\ServicioDeCorreo;
use App\DatosTransferencia\DatosDeCitaParaNotificacion;
use App\Events\CitaCancelada;
use App\Notifications\AvisoDeCitaCanceladaParaNegocio;
use App\Notifications\CitaCanceladaCliente;
use Illuminate\Contracts\Queue\ShouldQueue;

final class EnviarCorreosDeCitaCancelada implements ShouldQueue
{
    public function __construct(
        private readonly ServicioDeCorreo $servicioDeCorreo,
    ) {}

    public function handle(CitaCancelada $evento): void
    {
        $cita = $evento->cita;
        $datos = DatosDeCitaParaNotificacion::desde($cita);

        if ($datos->cliente->correo !== null) {
            $this->servicioDeCorreo->enviarAlCorreo($datos->cliente->correo, new CitaCanceladaCliente($cita));
        }

        $destinatarios = $datos->negocio->duenos();

        if ($datos->profesional->usuario !== null) {
            $destinatarios->push($datos->profesional->usuario);
        }

        $this->servicioDeCorreo->enviarA($destinatarios, new AvisoDeCitaCanceladaParaNegocio($cita));
    }
}
