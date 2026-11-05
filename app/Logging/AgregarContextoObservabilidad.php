<?php

declare(strict_types=1);

namespace App\Logging;

use App\Observabilidad\ContextoObservabilidad;
use Illuminate\Log\Logger;
use Monolog\Logger as RegistradorMonolog;
use Monolog\LogRecord;

/**
 * Tap de Monolog (config/logging.php) que agrega negocio_id, usuario_id
 * e id_peticion a cada línea de log de los canales `single` y `daily` —
 * así no hay que pasarlos a mano en cada Log::info()/error() del código,
 * ni cuando Laravel loguea una excepción no capturada por su cuenta.
 *
 * Sin integración de monitoreo externo (Sentry) todavía: cuando se
 * agregue, basta con configurarlo — ya lee los mismos canales/contexto,
 * nada de esto necesita cambiar.
 */
final class AgregarContextoObservabilidad
{
    public function __invoke(Logger $logger): void
    {
        $registrador = $logger->getLogger();

        if (! $registrador instanceof RegistradorMonolog) {
            return;
        }

        $contexto = app(ContextoObservabilidad::class);

        $registrador->pushProcessor(static function (LogRecord $registro) use ($contexto): LogRecord {
            $registro->extra = [...$registro->extra, ...$contexto->datos()];

            return $registro;
        });
    }
}
