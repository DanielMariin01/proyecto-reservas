<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;

test('recepcion no puede gestionar el catalogo', function (): void {
    $negocio = Negocio::factory()->create();
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);

    $respuesta = $this->actingAs($recepcion)->get("/app/{$negocio->slug}/servicios");

    $respuesta->assertForbidden();
});

test('profesional no puede gestionar el catalogo', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $respuesta = $this->actingAs($profesional)->get("/app/{$negocio->slug}/servicios");

    $respuesta->assertForbidden();
});

test('dueno si puede gestionar el catalogo', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/servicios");

    $respuesta->assertOk();
});
