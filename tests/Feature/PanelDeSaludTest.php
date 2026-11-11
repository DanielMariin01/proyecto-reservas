<?php

declare(strict_types=1);

use App\Actions\RegistrarErrorSistema;
use App\Models\Usuario;
use App\Notifications\AlertaColaDetenida;
use App\Notifications\AlertaErrorRepetido;
use App\Notifications\AlertaTareaSuscripcionesFallo;
use App\Services\ServicioSaludDelSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('el estado del sistema responde ok cuando todo esta bien', function (): void {
    $respuesta = $this->getJson('/estado');

    $respuesta->assertOk();
    $respuesta->assertJson(['estado' => 'ok']);
    $respuesta->assertJsonPath('verificaciones.colas', true);
});

test('el estado del sistema responde con fallo cuando la cola lleva detenida mas de 10 minutos', function (): void {
    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'available_at' => now()->subMinutes(15)->timestamp,
        'created_at' => now()->subMinutes(15)->timestamp,
    ]);

    $respuesta = $this->getJson('/estado');

    $respuesta->assertStatus(503);
    $respuesta->assertJson(['estado' => 'fallo']);
    $respuesta->assertJsonPath('verificaciones.colas', false);
});

test('colaDetenidaDesdeHace detecta un job pendiente viejo', function (): void {
    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'available_at' => now()->subMinutes(20)->timestamp,
        'created_at' => now()->subMinutes(20)->timestamp,
    ]);

    $minutos = app(ServicioSaludDelSistema::class)->colaDetenidaDesdeHace();

    expect($minutos)->not->toBeNull();
    expect($minutos)->toBeGreaterThanOrEqual(20);
});

test('un error 500 que se repite 5 veces en la misma ruta avisa al super admin, y un sexto no repite el aviso', function (): void {
    Notification::fake();

    $superAdmin = Usuario::factory()->superAdmin()->create();
    $peticion = Request::create('/ruta-de-prueba', 'GET');

    for ($i = 0; $i < 5; $i++) {
        app(RegistrarErrorSistema::class)->ejecutar(new RuntimeException('Fallo de prueba'), $peticion);
    }

    Notification::assertSentToTimes($superAdmin, AlertaErrorRepetido::class, 1);

    app(RegistrarErrorSistema::class)->ejecutar(new RuntimeException('Fallo de prueba'), $peticion);

    Notification::assertSentToTimes($superAdmin, AlertaErrorRepetido::class, 1);
});

test('un error 404 que se repite no avisa al super admin', function (): void {
    Notification::fake();

    $superAdmin = Usuario::factory()->superAdmin()->create();
    $peticion = Request::create('/ruta-404', 'GET');

    for ($i = 0; $i < 6; $i++) {
        app(RegistrarErrorSistema::class)->ejecutar(new NotFoundHttpException('No existe'), $peticion);
    }

    Notification::assertNothingSentTo($superAdmin);
});

test('verificar salud programada avisa cuando la cola esta detenida', function (): void {
    Notification::fake();

    $superAdmin = Usuario::factory()->superAdmin()->create();

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'available_at' => now()->subMinutes(15)->timestamp,
        'created_at' => now()->subMinutes(15)->timestamp,
    ]);

    Artisan::call('observabilidad:verificar-salud');

    Notification::assertSentToTimes($superAdmin, AlertaColaDetenida::class, 1);
});

// El filtro por negocio de Registro de errores y el reintento de
// Trabajos fallidos se movieron a RegistroErrorControllerTest y
// TrabajoFallidoControllerTest (T15: migracion de Filament — estas dos
// pantallas ya no viven en Filament, se prueban por HTTP, no por Livewire).

test('la tarea diaria de suscripciones no avisa de fallo cuando termina bien', function (): void {
    Notification::fake();

    Usuario::factory()->superAdmin()->create();

    // Sin negocios que transicionar, actualizarEstados() no tiene nada que
    // hacer y termina sin error: control negativo de que el try/catch
    // agregado no dispara la alerta de fallo en el camino feliz.
    $this->artisan('suscripciones:actualizar-estados')->assertSuccessful();

    Notification::assertNotSentTo(Usuario::all(), AlertaTareaSuscripcionesFallo::class);
});

test('la notificacion de fallo de la tarea de suscripciones incluye el mensaje del error', function (): void {
    $notificacion = new AlertaTareaSuscripcionesFallo('La conexión a la base de datos falló.');
    $correo = $notificacion->toMail(Usuario::factory()->superAdmin()->make());

    expect($correo->subject)->toBe('Falló la tarea diaria de suscripciones');
    expect(collect($correo->introLines)->implode(' '))->toContain('La conexión a la base de datos falló.');
});
