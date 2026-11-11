<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\CategoriaServicio;
use App\Models\Negocio;
use App\Models\Servicio;

test('un profesional no puede ver las paginas de servicios', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($profesional)->get("/app/{$negocio->slug}/servicios")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/servicios/create")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/servicios/{$servicio->id}/edit")->assertForbidden();
});

test('el dueno ve las 3 pantallas de servicios', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Corte clásico']);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/servicios")->assertOk()->assertSee('Servicios');
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/servicios/create")->assertOk();
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/servicios/{$servicio->id}/edit")->assertOk();
});

test('la api lista solo los servicios del negocio actual', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    Servicio::factory()->create(['negocio_id' => $negocioA->id, 'nombre' => 'Servicio A']);
    Servicio::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Servicio B']);

    $respuesta = $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/servicios");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['nombre'])->toBe('Servicio A');
});

test('un dueno no puede editar un servicio de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $servicioB = Servicio::factory()->create(['negocio_id' => $negocioB->id]);

    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/servicios/{$servicioB->id}/edit")->assertNotFound();
    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/servicios/{$servicioB->id}", [
        'nombre' => 'Hackeado',
        'duracion_minutos' => 30,
        'precio' => 10000,
    ])->assertNotFound();

    expect($servicioB->refresh()->nombre)->not->toBe('Hackeado');
});

test('crear un servicio lo asocia al negocio actual', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/servicios", [
        'nombre' => 'Corte + Barba',
        'duracion_minutos' => 45,
        'precio' => 30000,
        'visible' => true,
    ]);

    $respuesta->assertCreated();
    $this->assertDatabaseHas('servicios', ['negocio_id' => $negocio->id, 'nombre' => 'Corte + Barba']);
});

test('no se puede asignar una categoria de otro negocio al crear un servicio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $categoriaB = CategoriaServicio::factory()->create(['negocio_id' => $negocioB->id]);

    $respuesta = $this->actingAs($duenoA)->postJson("/app/{$negocioA->slug}/api/servicios", [
        'nombre' => 'Corte',
        'categoria_servicio_id' => $categoriaB->id,
        'duracion_minutos' => 30,
        'precio' => 10000,
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['categoria_servicio_id']);
});

test('actualizar un servicio cambia sus datos', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Nombre viejo']);

    $respuesta = $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/servicios/{$servicio->id}", [
        'nombre' => 'Nombre nuevo',
        'duracion_minutos' => 30,
        'precio' => 15000,
        'visible' => false,
    ]);

    $respuesta->assertOk();
    expect($servicio->refresh()->nombre)->toBe('Nombre nuevo');
    expect($servicio->visible)->toBeFalse();
});

test('eliminar un servicio lo borra', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/servicios/{$servicio->id}");

    $respuesta->assertOk();
    expect(Servicio::query()->find($servicio->id))->toBeNull();
});

test('reordenar actualiza el campo orden de cada servicio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $primero = Servicio::factory()->create(['negocio_id' => $negocio->id, 'orden' => 0]);
    $segundo = Servicio::factory()->create(['negocio_id' => $negocio->id, 'orden' => 1]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/servicios/reordenar", [
        'ids' => [$segundo->id, $primero->id],
    ]);

    $respuesta->assertOk();
    expect($segundo->refresh()->orden)->toBe(0);
    expect($primero->refresh()->orden)->toBe(1);
});
