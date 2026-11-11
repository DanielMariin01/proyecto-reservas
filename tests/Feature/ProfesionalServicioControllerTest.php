<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;

test('la api lista los servicios vinculados y los disponibles para vincular', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicioVinculado = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Vinculado']);
    $servicioLibre = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Libre']);
    $profesional->servicios()->attach($servicioVinculado->id, ['precio' => 15000, 'duracion_minutos' => 20]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/servicios");

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(1);
    expect($respuesta->json('data.0.precioPropio'))->toBe(15000);
    expect($respuesta->json('disponibles'))->toHaveCount(1);
    expect($respuesta->json('disponibles.0.id'))->toBe($servicioLibre->id);
});

test('no se puede vincular un servicio de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $profesionalA = Profesional::factory()->create(['negocio_id' => $negocioA->id]);
    $servicioB = Servicio::factory()->create(['negocio_id' => $negocioB->id]);

    $respuesta = $this->actingAs($duenoA)->postJson("/app/{$negocioA->slug}/api/profesionales/{$profesionalA->id}/servicios", [
        'servicio_id' => $servicioB->id,
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['servicio_id']);
});

test('vincular un servicio guarda el precio y la duracion propios', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/servicios", [
        'servicio_id' => $servicio->id,
        'precio' => 25000,
        'duracion_minutos' => 40,
    ]);

    $respuesta->assertCreated();

    $this->assertDatabaseHas('profesional_servicio', [
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'precio' => 25000,
        'duracion_minutos' => 40,
    ]);
});

test('actualizar el pivote cambia el precio propio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $profesional->servicios()->attach($servicio->id, ['precio' => 10000, 'duracion_minutos' => 30]);

    $respuesta = $this->actingAs($dueno)->putJson(
        "/app/{$negocio->slug}/api/profesionales/{$profesional->id}/servicios/{$servicio->id}",
        ['precio' => 18000, 'duracion_minutos' => 30],
    );

    $respuesta->assertOk();
    $this->assertDatabaseHas('profesional_servicio', [
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'precio' => 18000,
    ]);
});

test('desvincular un servicio lo quita de la tabla pivote', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $profesional->servicios()->attach($servicio->id);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/servicios/{$servicio->id}");

    $respuesta->assertOk();
    $this->assertDatabaseMissing('profesional_servicio', ['profesional_id' => $profesional->id, 'servicio_id' => $servicio->id]);
});
