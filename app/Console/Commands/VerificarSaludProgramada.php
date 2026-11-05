<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Notifications\AlertaColaDetenida;
use App\Services\ServicioSaludDelSistema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

/**
 * RNF-OBS-04: a diferencia de /estado (que espera a que un monitor
 * externo pregunte), este comando avisa por su cuenta cuando la cola
 * lleva detenida más de lo normal — con un enfriamiento de 1 hora para
 * no mandar un correo nuevo cada 10 minutos mientras el problema sigue.
 */
final class VerificarSaludProgramada extends Command
{
    private const ENFRIAMIENTO_ALERTA_MINUTOS = 60;

    protected $signature = 'observabilidad:verificar-salud';

    protected $description = 'Avisa al super admin si la cola de trabajos dejó de procesarse.';

    public function handle(ServicioSaludDelSistema $servicio): int
    {
        $minutosDetenida = $servicio->colaDetenidaDesdeHace();

        if ($minutosDetenida === null) {
            return self::SUCCESS;
        }

        $clave = 'alerta-cola-detenida';

        if (Cache::has($clave)) {
            return self::SUCCESS;
        }

        Cache::put($clave, true, now()->addMinutes(self::ENFRIAMIENTO_ALERTA_MINUTOS));

        Notification::send(
            Usuario::where('es_super_admin', true)->get(),
            new AlertaColaDetenida($minutosDetenida),
        );

        return self::SUCCESS;
    }
}
