<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contratos\ServicioDeCorreo;
use App\DatosTransferencia\DatosDeCitaParaNotificacion;
use App\Events\CitaReprogramada;
use App\Notifications\AvisoDeCitaReprogramadaParaNegocio;
use App\Notifications\CitaReprogramadaCliente;
use Illuminate\Contracts\Queue\ShouldQueue;

final class EnviarCorreosDeCitaReprogramada implements ShouldQueue
{
    public function __construct(
        private readonly ServicioDeCorreo $servicioDeCorreo,
    ) {}

    public function handle(CitaReprogramada $evento): void
    {
        $cita = $evento->cita;
        $datos = DatosDeCitaParaNotificacion::desde($cita);

        if ($datos->cliente->correo !== null) {
            $this->servicioDeCorreo->enviarAlCorreo(
                $datos->cliente->correo,
                new CitaReprogramadaCliente($cita, $evento->inicioAnterior),
            );
        }

        $destinatarios = $datos->negocio->duenos();

        if ($datos->profesional->usuario !== null) {
            $destinatarios->push($datos->profesional->usuario);
        }

        $this->servicioDeCorreo->enviarA(
            $destinatarios,
            new AvisoDeCitaReprogramadaParaNegocio($cita, $evento->inicioAnterior),
        );
    }
}
