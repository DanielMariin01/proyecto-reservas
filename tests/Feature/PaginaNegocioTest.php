<?php

declare(strict_types=1);

use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;

test('la pagina publica de un negocio activo responde con su nombre', function (): void {
    $negocio = Negocio::factory()->create(['nombre' => 'BarberJapo']);

    $respuesta = $this->get("/{$negocio->slug}");

    $respuesta->assertOk();
    $respuesta->assertSee('BarberJapo');
});

test('la pagina publica de un negocio suspendido muestra que no esta disponible', function (): void {
    $negocio = Negocio::factory()->create(['estado_suscripcion' => EstadoSuscripcion::Suspendido]);

    $respuesta = $this->get("/{$negocio->slug}");

    $respuesta->assertStatus(404);
    $respuesta->assertSee('no está disponible');
});

test('una pagina publica con slug inexistente responde 404', function (): void {
    $respuesta = $this->get('/negocio-que-no-existe');

    $respuesta->assertStatus(404);
});

test('las rutas internas siguen funcionando sin que el comodin de slug las atrape', function (): void {
    $this->get('/ingresar')->assertOk();
});
