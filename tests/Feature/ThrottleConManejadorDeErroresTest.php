<?php

declare(strict_types=1);

use App\Models\Negocio;

/**
 * El manejador de errores amigable solo se activa con APP_DEBUG=false (ver
 * AutenticacionConManejadorDeErroresTest) - este entorno local tenia
 * APP_DEBUG=true en el .env real (nunca se regenero despues del fix de ese
 * archivo), por lo que cualquier excepcion, incluyendo el 429 del
 * middleware "throttle", se escapaba al manejador nativo de Laravel con
 * traza completa. ManejadorRespuestaDeError.php:44 ya resuelve el status
 * via HttpExceptionInterface de forma generica (no necesita saber de
 * ThrottleRequestsException en particular), asi que esta prueba solo
 * necesita apagar debug para confirmar que el 429 real pasa por ahi.
 */
test('un 429 por exceder el limite de intentos responde con el mensaje amigable, no con la traza', function (): void {
    config(['app.debug' => false]);
    $negocio = Negocio::factory()->create();

    for ($intento = 0; $intento < 5; $intento++) {
        $this->postJson("/api/publico/{$negocio->slug}/reservas", []);
    }

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas", []);

    $respuesta->assertStatus(429);
    $respuesta->assertJsonStructure(['mensaje', 'codigoReferencia']);
    expect($respuesta->json('mensaje'))->toContain('Demasiados intentos');
    expect($respuesta->json('mensaje'))->not->toContain('Exception');
    expect($respuesta->json('mensaje'))->not->toContain('.php');
});

test('el 429 trae la cabecera Retry-After para que el frontend sepa cuanto esperar', function (): void {
    config(['app.debug' => false]);
    $negocio = Negocio::factory()->create();

    for ($intento = 0; $intento < 5; $intento++) {
        $this->postJson("/api/publico/{$negocio->slug}/reservas", []);
    }

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas", []);

    $respuesta->assertStatus(429);
    $respuesta->assertHeader('Retry-After');
    expect((int) $respuesta->headers->get('Retry-After'))->toBeGreaterThan(0);
});
