<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;

test('un usuario de un negocio suspendido no puede usar el panel', function (): void {
    $negocio = Negocio::factory()->suspendido()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}");

    $respuesta->assertForbidden();
    $respuesta->assertSee('Tu cuenta está suspendida');
});

test('un usuario de un negocio cancelado no puede usar el panel', function (): void {
    $negocio = Negocio::factory()->cancelado()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}");

    $respuesta->assertForbidden();
    $respuesta->assertSee('Este negocio fue cancelado');
});

test('un negocio activo permite el acceso normal', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}");

    $respuesta->assertOk();
});
