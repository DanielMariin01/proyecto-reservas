<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Observabilidad\ContextoObservabilidad;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

test('cada peticion lleva un id de peticion distinto en el header X-Id-Peticion', function (): void {
    $primera = $this->get('/up');
    $segunda = $this->get('/up');

    $idPrimera = $primera->headers->get('X-Id-Peticion');
    $idSegunda = $segunda->headers->get('X-Id-Peticion');

    expect($idPrimera)->not->toBeEmpty();
    expect($idSegunda)->not->toBeEmpty();
    expect($idPrimera)->not->toBe($idSegunda);
});

test('un log escrito con un tenant y un usuario activos incluye negocio_id, usuario_id e id_peticion', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);
    Auth::login($dueno);

    $idPeticion = (string) Str::uuid();
    app(ContextoObservabilidad::class)->definirIdPeticion($idPeticion);

    $rutaLog = storage_path('logs/prueba-observabilidad.log');
    @unlink($rutaLog);
    config(['logging.channels.single.path' => $rutaLog]);

    Log::channel('single')->error('Mensaje de prueba para observabilidad');

    $lineas = array_values(array_filter(explode("\n", (string) file_get_contents($rutaLog))));
    expect($lineas)->not->toBeEmpty();

    $registro = json_decode(end($lineas), true, flags: JSON_THROW_ON_ERROR);

    expect($registro['extra']['negocio_id'] ?? null)->toBe($negocio->id);
    expect($registro['extra']['usuario_id'] ?? null)->toBe($dueno->id);
    expect($registro['extra']['id_peticion'] ?? null)->toBe($idPeticion);

    @unlink($rutaLog);
});

test('la api publica responde con mensaje en lenguaje simple y codigo de referencia cuando algo falla', function (): void {
    config(['app.debug' => false]);

    $respuesta = $this->getJson('/api/publico/este-negocio-no-existe-de-verdad/pagina');

    $respuesta->assertStatus(404);
    $respuesta->assertJsonStructure(['mensaje', 'codigoReferencia']);

    $codigo = $respuesta->json('codigoReferencia');
    expect($codigo)->toMatch('/^REF-[A-Z0-9]{8}$/');
    expect($respuesta->json('mensaje'))->toContain($codigo);
    expect($respuesta->headers->get('X-Id-Peticion'))->not->toBeEmpty();
});

test('la api publica sigue devolviendo los errores de validacion en su formato normal', function (): void {
    config(['app.debug' => false]);

    $negocio = Negocio::factory()->create();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas", []);

    $respuesta->assertStatus(422);
    $respuesta->assertJsonStructure(['message', 'errors']);
});

test('una peticion que no es json muestra la vista generica de error con el codigo de referencia', function (): void {
    config(['app.debug' => false]);

    $respuesta = $this->get('/esta-ruta-no-existe-de-verdad-12345');

    $respuesta->assertStatus(404);
    $respuesta->assertViewIs('error-generico');
    $respuesta->assertSee('REF-', false);
});

test('en modo debug la peticion fallida no pasa por el manejador propio', function (): void {
    config(['app.debug' => true]);

    $respuesta = $this->get('/esta-ruta-no-existe-de-verdad-12345');

    $respuesta->assertStatus(404);
    $respuesta->assertDontSee('Reintentar');
});
