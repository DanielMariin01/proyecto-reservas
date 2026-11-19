<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\CategoriaProducto;
use App\Models\Negocio;

test('un profesional no puede ver las paginas de categorias de producto', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $categoria = CategoriaProducto::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($profesional)->get("/app/{$negocio->slug}/categorias-producto")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/categorias-producto/create")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/categorias-producto/{$categoria->id}/edit")->assertForbidden();
});

test('el dueno ve las 3 pantallas de categorias de producto', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $categoria = CategoriaProducto::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Cuidado capilar']);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/categorias-producto")->assertOk()->assertSee('Categorías de producto');
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/categorias-producto/create")->assertOk();
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/categorias-producto/{$categoria->id}/edit")->assertOk();
});

test('la api lista solo las categorias del negocio actual con el conteo de productos', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $categoriaA = CategoriaProducto::factory()->create(['negocio_id' => $negocioA->id, 'nombre' => 'Cuidado capilar']);
    $categoriaA->productos()->create([
        'negocio_id' => $negocioA->id,
        'nombre' => 'Shampoo anticaspa',
        'precio' => 25000,
    ]);
    CategoriaProducto::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Otra']);

    $respuesta = $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/categorias-producto");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['nombre'])->toBe('Cuidado capilar');
    expect($datos[0]['productosCount'])->toBe(1);
});

test('un dueno no puede editar una categoria de producto de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $categoriaB = CategoriaProducto::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Original']);

    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/categorias-producto/{$categoriaB->id}/edit")->assertNotFound();
    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/categorias-producto/{$categoriaB->id}", ['nombre' => 'Hackeada'])
        ->assertNotFound();

    expect($categoriaB->refresh()->nombre)->toBe('Original');
});

test('crear una categoria de producto la asocia al negocio actual', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/categorias-producto", ['nombre' => 'Aseo personal']);

    $respuesta->assertCreated();
    $this->assertDatabaseHas('categorias_producto', ['negocio_id' => $negocio->id, 'nombre' => 'Aseo personal']);
    expect($respuesta->json('data.orden'))->toBe(0);
});

test('eliminar una categoria de producto la borra', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $categoria = CategoriaProducto::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/categorias-producto/{$categoria->id}");

    $respuesta->assertOk();
    expect(CategoriaProducto::query()->find($categoria->id))->toBeNull();
});

test('reordenar actualiza el campo orden de cada categoria de producto', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $primera = CategoriaProducto::factory()->create(['negocio_id' => $negocio->id, 'orden' => 0]);
    $segunda = CategoriaProducto::factory()->create(['negocio_id' => $negocio->id, 'orden' => 1]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/categorias-producto/reordenar", [
        'ids' => [$segunda->id, $primera->id],
    ]);

    $respuesta->assertOk();
    expect($segunda->refresh()->orden)->toBe(0);
    expect($primera->refresh()->orden)->toBe(1);
});
