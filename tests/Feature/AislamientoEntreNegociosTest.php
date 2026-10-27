<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;

test('un dueno no puede acceder al panel de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();

    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $respuesta = $this->actingAs($duenoA)->get("/app/{$negocioB->slug}");

    $respuesta->assertNotFound();
});

test('un dueno no ve usuarios de otro negocio en su panel', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();

    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $duenoB = crearUsuarioConRol($negocioB, RolNegocio::Dueno, ['email' => 'otro-negocio@test.com']);

    $respuesta = $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/usuarios");

    $respuesta->assertOk();
    $respuesta->assertDontSee($duenoB->email);
});

test('un dueno no puede ver el panel admin', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get('/admin');

    $respuesta->assertForbidden();
});
