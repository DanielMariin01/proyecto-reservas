<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\CategoriaServicio;
use App\Models\Negocio;

test('un profesional no puede ver las paginas de categorias de servicio', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $categoria = CategoriaServicio::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($profesional)->get("/app/{$negocio->slug}/categorias-servicio")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/categorias-servicio/create")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/categorias-servicio/{$categoria->id}/edit")->assertForbidden();
});

test('el dueno ve las 3 pantallas de categorias de servicio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $categoria = CategoriaServicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Cortes']);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/categorias-servicio")->assertOk()->assertSee('Categorías de servicio');
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/categorias-servicio/create")->assertOk();
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/categorias-servicio/{$categoria->id}/edit")->assertOk();
});

test('la api lista solo las categorias del negocio actual con el conteo de servicios', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $categoriaA = CategoriaServicio::factory()->create(['negocio_id' => $negocioA->id, 'nombre' => 'Cortes']);
    $categoriaA->servicios()->create([
        'negocio_id' => $negocioA->id,
        'nombre' => 'Corte clásico',
        'duracion_minutos' => 30,
        'precio' => 20000,
    ]);
    CategoriaServicio::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Otra']);

    $respuesta = $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/categorias-servicio");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['nombre'])->toBe('Cortes');
    expect($datos[0]['serviciosCount'])->toBe(1);
});

test('un dueno no puede editar una categoria de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $categoriaB = CategoriaServicio::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Original']);

    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/categorias-servicio/{$categoriaB->id}/edit")->assertNotFound();
    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/categorias-servicio/{$categoriaB->id}", ['nombre' => 'Hackeada'])
        ->assertNotFound();

    expect($categoriaB->refresh()->nombre)->toBe('Original');
});

test('crear una categoria la asocia al negocio actual', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/categorias-servicio", ['nombre' => 'Barba']);

    $respuesta->assertCreated();
    $this->assertDatabaseHas('categorias_servicio', ['negocio_id' => $negocio->id, 'nombre' => 'Barba']);
});

test('eliminar una categoria la borra', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $categoria = CategoriaServicio::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/categorias-servicio/{$categoria->id}");

    $respuesta->assertOk();
    expect(CategoriaServicio::query()->find($categoria->id))->toBeNull();
});

test('reordenar actualiza el campo orden de cada categoria', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $primera = CategoriaServicio::factory()->create(['negocio_id' => $negocio->id, 'orden' => 0]);
    $segunda = CategoriaServicio::factory()->create(['negocio_id' => $negocio->id, 'orden' => 1]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/categorias-servicio/reordenar", [
        'ids' => [$segunda->id, $primera->id],
    ]);

    $respuesta->assertOk();
    expect($segunda->refresh()->orden)->toBe(0);
    expect($primera->refresh()->orden)->toBe(1);
});
