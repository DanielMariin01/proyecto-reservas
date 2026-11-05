<?php

declare(strict_types=1);

namespace App\Services;

use App\DatosTransferencia\ResultadoSaludSistema;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * RNF-OBS-05: las 4 verificaciones de la ruta /estado. RNF-OBS-04
 * reutiliza colaDetenidaDesdeHace() para la alerta de "cola detenida"
 * (App\Console\Commands\VerificarSaludProgramada), así el umbral de
 * minutos vive en un solo sitio.
 */
final class ServicioSaludDelSistema
{
    private const MINUTOS_COLA_DETENIDA = 10;

    public function verificar(): ResultadoSaludSistema
    {
        return new ResultadoSaludSistema(
            baseDatos: $this->verificarBaseDeDatos(),
            colas: $this->colaDetenidaDesdeHace() === null,
            almacenamiento: $this->verificarAlmacenamiento(),
            correo: $this->verificarCorreo(),
        );
    }

    /**
     * No hay un worker persistente (hosting compartido): el cron corre
     * `queue:work --stop-when-empty` cada minuto. Si el job pendiente más
     * antiguo lleva más de MINUTOS_COLA_DETENIDA esperando, lo más
     * probable es que el cron dejó de ejecutarse.
     */
    public function colaDetenidaDesdeHace(): ?int
    {
        $jobMasAntiguo = DB::table('jobs')->min('created_at');

        if ($jobMasAntiguo === null) {
            return null;
        }

        $minutos = (int) CarbonImmutable::createFromTimestamp((int) $jobMasAntiguo)->diffInMinutes(now());

        return $minutos >= self::MINUTOS_COLA_DETENIDA ? $minutos : null;
    }

    private function verificarBaseDeDatos(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function verificarAlmacenamiento(): bool
    {
        try {
            $ruta = 'salud/prueba-'.Str::uuid()->toString().'.txt';
            Storage::disk('local')->put($ruta, 'ok');
            $contenido = Storage::disk('local')->get($ruta);
            Storage::disk('local')->delete($ruta);

            return $contenido === 'ok';
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Solo revisa que el mailer configurado tenga lo necesario para
     * funcionar — no manda un correo real en cada chequeo, porque un
     * monitor externo puede pegarle a /estado cada 1-5 minutos.
     */
    private function verificarCorreo(): bool
    {
        $mailer = config('mail.default');
        $configuracion = config("mail.mailers.{$mailer}");

        if (! is_array($configuracion)) {
            return false;
        }

        return match ($configuracion['transport'] ?? null) {
            'smtp' => filled($configuracion['host'] ?? null),
            'ses', 'sendmail', 'log', 'array' => true,
            default => filled($configuracion['transport'] ?? null),
        };
    }
}
