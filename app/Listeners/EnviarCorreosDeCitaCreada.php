<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contratos\ServicioDeCorreo;
use App\DatosTransferencia\DatosDeCitaParaNotificacion;
use App\Events\CitaCreada;
use App\Notifications\AvisoDeCitaNuevaParaNegocio;
use App\Notifications\ConfirmacionDeCitaCliente;
use Illuminate\Contracts\Queue\ShouldQueue;

final class EnviarCorreosDeCitaCreada implements ShouldQueue
{
    public function __construct(
        private readonly ServicioDeCorreo $servicioDeCorreo,
    ) {}

    public function handle(CitaCreada $evento): void
    {
        $cita = $evento->cita;
        $datos = DatosDeCitaParaNotificacion::desde($cita);

        if ($datos->cliente->correo !== null) {
            $this->servicioDeCorreo->enviarAlCorreo($datos->cliente->correo, new ConfirmacionDeCitaCliente($cita));
        }

        $destinatarios = $datos->negocio->duenos();

        if ($datos->profesional->usuario !== null) {
            $destinatarios->push($datos->profesional->usuario);
        }

        // Si el dueño es tambien el profesional de esta cita, el push de
        // arriba lo deja dos veces en la lista - sin este unique() le
        // llegaria el mismo correo duplicado.
        $this->servicioDeCorreo->enviarA($destinatarios->unique('id'), new AvisoDeCitaNuevaParaNegocio($cita));
    }
}
