<?php

declare(strict_types=1);

use App\Actions\RegistrarErrorSistema;
use App\Filament\Resources\RegistroErrores\Pages\ListRegistroErrores;
use App\Filament\Resources\TrabajosFallidos\Pages\ListTrabajosFallidos;
use App\Models\Negocio;
use App\Models\RegistroError;
use App\Models\TrabajoFallido;
use App\Models\Usuario;
use App\Notifications\AlertaColaDetenida;
use App\Notifications\AlertaErrorRepetido;
use App\Notifications\AlertaTareaSuscripcionesFallo;
use App\Services\ServicioSaludDelSistema;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
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

test('el recurso de errores filtra por negocio', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();

    $errorA = RegistroError::create([
        'negocio_id' => $negocioA->id,
        'codigo_referencia' => 'REF-AAAAAAAA',
        'clase_excepcion' => RuntimeException::class,
        'mensaje' => 'Error del negocio A',
        'ruta' => 'ruta-a',
        'metodo_http' => 'GET',
        'estado_http' => 500,
    ]);
    $errorB = RegistroError::create([
        'negocio_id' => $negocioB->id,
        'codigo_referencia' => 'REF-BBBBBBBB',
        'clase_excepcion' => RuntimeException::class,
        'mensaje' => 'Error del negocio B',
        'ruta' => 'ruta-b',
        'metodo_http' => 'GET',
        'estado_http' => 500,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(ListRegistroErrores::class)
        ->assertCanSeeTableRecords([$errorA, $errorB])
        ->filterTable('negocio_id', $negocioA->id)
        ->assertCanSeeTableRecords([$errorA])
        ->assertCanNotSeeTableRecords([$errorB]);
});

test('reintentar un trabajo fallido lo vuelve a poner en la cola', function (): void {
    $uuid = (string) Str::uuid();

    DB::table('failed_jobs')->insert([
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Notifications\\AlertaColaDetenida']),
        'exception' => 'Excepción de prueba',
        'failed_at' => now(),
    ]);

    $trabajo = TrabajoFallido::query()->where('uuid', $uuid)->firstOrFail();

    expect($trabajo->tipo)->toBe('Otro');

    $superAdmin = Usuario::factory()->superAdmin()->create();
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(ListTrabajosFallidos::class)
        ->callTableAction('reintentar', $trabajo);

    expect(DB::table('failed_jobs')->where('uuid', $uuid)->exists())->toBeFalse();
    expect(DB::table('jobs')->count())->toBe(1);
});

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
