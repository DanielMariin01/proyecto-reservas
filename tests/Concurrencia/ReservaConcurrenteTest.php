<?php

declare(strict_types=1);

use App\Enums\EstadoCitaValor;
use App\Exceptions\HoraYaTomadaException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

beforeEach(function (): void {
    if (! Schema::hasTable('citas')) {
        $this->artisan('migrate', ['--force' => true]);
    }
});

/**
 * Caso de aceptación de CU-01: "dos reservas simultáneas a la misma franja
 * dejan exactamente una cita". Se usan dos procesos de PHP reales (no dos
 * llamadas dentro del mismo proceso) para que el bloqueo de fila del
 * profesional se comporte exactamente como en producción, cada uno con su
 * propia conexión a MariaDB.
 *
 * Para que la prueba no pueda dar un falso positivo por timing (que los
 * dos procesos, por azar, terminen ejecutándose en serie en vez de en
 * paralelo, "demostrando" la protección sin haberla puesto a prueba de
 * verdad), ambos esperan activamente hasta un instante futuro común antes
 * de llamar a reservar(), y la prueba verifica que de verdad coincidieron
 * en el tiempo antes de aceptar el resultado.
 */
test('dos reservas simultaneas para el mismo profesional y la misma hora dejan exactamente una cita', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 30]);
    $clienteA = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $clienteB = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $inicio = CarbonImmutable::now('UTC')->addDay()->setTime(15, 0);

    $script = base_path('tests/herramientas/intentar_reservar.php');
    $resultadoA = sys_get_temp_dir().'/reserva_concurrente_a_'.uniqid().'.json';
    $resultadoB = sys_get_temp_dir().'/reserva_concurrente_b_'.uniqid().'.json';

    // 1.5 s de margen para que ambos procesos terminen de arrancar PHP y
    // Laravel y lleguen a la espera activa antes del instante objetivo.
    $objetivo = microtime(true) + 1.5;

    $procesoA = new Process(['php', $script, (string) $profesional->id, (string) $servicio->id, (string) $clienteA->id, $inicio->toIso8601String(), (string) $objetivo, $resultadoA]);
    $procesoB = new Process(['php', $script, (string) $profesional->id, (string) $servicio->id, (string) $clienteB->id, $inicio->toIso8601String(), (string) $objetivo, $resultadoB]);
    $procesoA->setTimeout(30);
    $procesoB->setTimeout(30);

    $procesoA->start();
    $procesoB->start();
    $procesoA->wait();
    $procesoB->wait();

    try {
        expect($procesoA->isSuccessful())->toBeTrue("Proceso A falló: {$procesoA->getErrorOutput()}");
        expect($procesoB->isSuccessful())->toBeTrue("Proceso B falló: {$procesoB->getErrorOutput()}");

        $datosA = json_decode((string) file_get_contents($resultadoA), true);
        $datosB = json_decode((string) file_get_contents($resultadoB), true);

        expect($datosA)->not->toBeNull('El proceso A no escribió su resultado.');
        expect($datosB)->not->toBeNull('El proceso B no escribió su resultado.');

        // Si no coincidieron de verdad en el tiempo, la prueba no demuestra
        // nada: falla en vez de dar un falso positivo.
        $separacion = abs($datosA['momentoDeIntento'] - $datosB['momentoDeIntento']);
        expect($separacion)->toBeLessThan(
            0.3,
            "Los dos procesos no coincidieron en el tiempo (separación: {$separacion}s); repetir la prueba.",
        );

        $resultados = [$datosA['resultado'], $datosB['resultado']];
        sort($resultados);
        expect($resultados)->toBe(['exito', 'fallo']);

        $fallido = $datosA['resultado'] === 'fallo' ? $datosA : $datosB;
        expect($fallido['excepcion'])->toBe(HoraYaTomadaException::class);

        $citasEnLaFranja = Cita::query()
            ->where('profesional_id', $profesional->id)
            ->where('inicio', $inicio)
            ->get();

        expect($citasEnLaFranja)->toHaveCount(1);
        expect($citasEnLaFranja->first()->estado)->toBe(EstadoCitaValor::Confirmada);
    } finally {
        @unlink($resultadoA);
        @unlink($resultadoB);
        Negocio::query()->whereKey($negocio->id)->delete();
    }
});
