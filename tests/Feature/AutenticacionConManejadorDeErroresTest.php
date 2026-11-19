<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

/**
 * El manejador de errores amigable (bootstrap/app.php, ManejadorRespuestaDeError)
 * solo se activa con APP_DEBUG=false -el valor real de producción-, así
 * que estas pruebas necesitaban "apagar" el modo debug a propósito para
 * reproducir el bug: antes de excluir AuthenticationException ahí mismo,
 * cualquier visitante sin sesión en una ruta protegida por "auth" caía
 * en un 500 genérico en vez de la redirección/401 normal de Laravel.
 * Con APP_DEBUG=true (como estaba en todo el CI hasta ahora) esto nunca
 * se manifestaba, de ahí que pasara desapercibido.
 */
test('un invitado sin sesion que visita /admin es redirigido, no revienta en 500', function (): void {
    config(['app.debug' => false]);

    $respuesta = $this->get('/admin');

    $respuesta->assertRedirect(route('login'));
});

test('un invitado sin sesion que visita el panel de un negocio es redirigido, no revienta en 500', function (): void {
    config(['app.debug' => false]);
    $negocio = Negocio::factory()->create();

    $respuesta = $this->get("/app/{$negocio->slug}");

    $respuesta->assertRedirect(route('login'));
});

test('una api que espera json responde 401 a un invitado, no 500', function (): void {
    config(['app.debug' => false]);

    $respuesta = $this->getJson('/mi-cuenta/api/citas');

    $respuesta->assertUnauthorized();
});

test('un usuario del guard equivocado tambien recibe 401 limpio, no 500', function (): void {
    config(['app.debug' => false]);
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($usuario, 'web')->getJson('/mi-cuenta/api/citas');

    $respuesta->assertUnauthorized();
    Auth::guard('web')->logout();
});
