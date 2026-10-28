<?php

declare(strict_types=1);

/**
 * Script independiente (no es parte de la aplicación) para la prueba de
 * concurrencia real de ServicioReservas. Se lanza como un proceso de PHP
 * aparte -con su propia conexión a la base de datos- para que el bloqueo
 * de fila del profesional (SELECT ... FOR UPDATE) se comporte exactamente
 * como en producción: dos transacciones de verdad, no dos llamadas dentro
 * del mismo proceso envueltas en la transacción de prueba de Pest.
 *
 * Argumentos: profesionalId servicioId clienteId inicioIso objetivoMicrotime archivoResultado
 *
 * Espera activamente hasta $objetivoMicrotime (un instante futuro calculado
 * por el proceso que lanza ambas copias de este script) antes de llamar a
 * ServicioReservas::reservar(), para que las dos copias intenten reservar
 * en el mismo instante real y no por azar de cuál arrancó primero.
 */

require __DIR__.'/../../vendor/autoload.php';

use App\Enums\OrigenCita;
use App\Models\Cliente;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioReservas;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Throwable;

[, $profesionalId, $servicioId, $clienteId, $inicioIso, $objetivoMicrotime, $archivoResultado] = $argv;

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

while (microtime(true) < (float) $objetivoMicrotime) {
    usleep(200);
}

$momentoDeIntento = microtime(true);

try {
    $profesional = Profesional::findOrFail((int) $profesionalId);
    $servicio = Servicio::findOrFail((int) $servicioId);
    $cliente = Cliente::findOrFail((int) $clienteId);
    $inicio = CarbonImmutable::parse($inicioIso, 'UTC');

    $cita = $app->make(ServicioReservas::class)->reservar($profesional, $servicio, $cliente, $inicio, OrigenCita::Web);

    file_put_contents($archivoResultado, json_encode([
        'resultado' => 'exito',
        'citaId' => $cita->id,
        'momentoDeIntento' => $momentoDeIntento,
    ]));
} catch (Throwable $excepcion) {
    file_put_contents($archivoResultado, json_encode([
        'resultado' => 'fallo',
        'excepcion' => $excepcion::class,
        'momentoDeIntento' => $momentoDeIntento,
    ]));
}
