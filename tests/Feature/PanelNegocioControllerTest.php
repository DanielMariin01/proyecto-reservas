<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;

test('un dueno ve el panel de su negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}");

    $respuesta->assertOk()->assertSee($negocio->nombre);
});

test('un dueno no puede ver el panel de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $respuesta = $this->actingAs($duenoA)->get("/app/{$negocioB->slug}");

    $respuesta->assertNotFound();
});

test('un negocio suspendido muestra la pantalla de suspension', function (): void {
    $negocio = Negocio::factory()->suspendido()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}");

    $respuesta->assertForbidden();
});

test('sin sesion se redirige a ingresar', function (): void {
    $negocio = Negocio::factory()->create();

    $respuesta = $this->get("/app/{$negocio->slug}");

    $respuesta->assertRedirect(route('login'));
});
