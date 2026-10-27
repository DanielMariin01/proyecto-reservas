<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Notifications\AvisoSuscripcionProximaAVencer;
use App\Notifications\ResumenDiarioNegociosVencidosParaSuperAdmin;
use App\Services\ServicioSuscripcion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

final class ActualizarEstadosSuscripciones extends Command
{
    protected $signature = 'suscripciones:actualizar-estados';

    protected $description = 'Transiciona el estado de los negocios según sus fechas de pago y envía los avisos de la sección 14.5.';

    public function handle(ServicioSuscripcion $servicioSuscripcion): int
    {
        $servicioSuscripcion->actualizarEstados();

        $this->avisarVencimientosProximos($servicioSuscripcion);
        $this->avisarAlSuperAdmin($servicioSuscripcion);

        return self::SUCCESS;
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
