<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;

test('un servicio no acepta menos de 5 minutos de duracion', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/servicios", [
        'nombre' => 'Corte rápido',
        'duracion_minutos' => 3,
        'precio' => 10000,
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['duracion_minutos']);
});

test('un servicio con 5 minutos o mas se guarda sin error', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/servicios", [
        'nombre' => 'Corte rápido',
        'duracion_minutos' => 5,
        'precio' => 10000,
    ]);

    $respuesta->assertCreated();

    $this->assertDatabaseHas('servicios', [
        'negocio_id' => $negocio->id,
        'nombre' => 'Corte rápido',
    ]);
});
