<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Notifications\AlertaTareaSuscripcionesFallo;
use App\Notifications\AvisoSuscripcionProximaAVencer;
use App\Notifications\ResumenDiarioNegociosVencidosParaSuperAdmin;
use App\Services\ServicioSuscripcion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Throwable;

final class ActualizarEstadosSuscripciones extends Command
{
    protected $signature = 'suscripciones:actualizar-estados';

    protected $description = 'Transiciona el estado de los negocios según sus fechas de pago y envía los avisos de la sección 14.5.';

    public function handle(ServicioSuscripcion $servicioSuscripcion): int
    {
        /**
         * RNF-OBS-04: esta tarea es crítica (de ella depende que los
         * negocios se suspendan/reactiven a tiempo), así que avisa al
         * super admin ante la primera falla, sin esperar a que se repita
         * como el resto de errores. Relanza la excepción para que además
         * quede registrada igual que cualquier otro error (report() de
         * bootstrap/app.php) y el comando termine con código de fallo.
         */
        try {
            $servicioSuscripcion->actualizarEstados();
        } catch (Throwable $excepcion) {
            $this->avisarFalloAlSuperAdmin($excepcion);

            throw $excepcion;
        }

        $this->avisarVencimientosProximos($servicioSuscripcion);
        $this->avisarAlSuperAdmin($servicioSuscripcion);

        return self::SUCCESS;
    }

    private function avisarFalloAlSuperAdmin(Throwable $excepcion): void
    {
        $superAdmins = Usuario::where('es_super_admin', true)->get();

        Notification::send($superAdmins, new AlertaTareaSuscripcionesFallo($excepcion->getMessage()));
    }

    private function avisarVencimientosProximos(ServicioSuscripcion $servicioSuscripcion): void
    {
        foreach ($servicioSuscripcion->negociosQueVencenEnDias(5) as $negocio) {
            Notification::send($negocio->duenos(), new AvisoSuscripcionProximaAVencer($negocio));
        }
    }

    private function avisarAlSuperAdmin(ServicioSuscripcion $servicioSuscripcion): void
    {
        $superAdmins = Usuario::where('es_super_admin', true)->get();

        Notification::send($superAdmins, new ResumenDiarioNegociosVencidosParaSuperAdmin(
            $servicioSuscripcion->negociosVencidos(),
        ));
    }
}
