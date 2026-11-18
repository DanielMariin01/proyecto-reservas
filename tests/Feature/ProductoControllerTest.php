<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;

test('un profesional no puede ver las paginas de productos', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($profesional)->get("/app/{$negocio->slug}/productos")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/productos/create")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/productos/{$producto->id}/edit")->assertForbidden();
});

test('el dueno ve las 3 pantallas de productos', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Cera para barba']);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/productos")->assertOk()->assertSee('Productos');
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/productos/create")->assertOk();
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/productos/{$producto->id}/edit")->assertOk();
});

test('la api lista solo los productos del negocio actual', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    Producto::factory()->create(['negocio_id' => $negocioA->id, 'nombre' => 'Producto A']);
    Producto::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Producto B']);

    $respuesta = $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/productos");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['nombre'])->toBe('Producto A');
});

test('la api devuelve la foto del producto como url servible, no como la ruta cruda del disco', function (): void {
    Storage::fake('public');
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id, 'foto' => 'productos/cera.jpg']);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/productos");

    $respuesta->assertOk();
    expect($respuesta->json('data.0.foto'))->toBe(Storage::disk('public')->url('productos/cera.jpg'));
    expect($respuesta->json('data.0.foto'))->not->toBe($producto->foto);
});

test('un dueno no puede editar un producto de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $productoB = Producto::factory()->create(['negocio_id' => $negocioB->id]);

    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/productos/{$productoB->id}/edit")->assertNotFound();
    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/productos/{$productoB->id}", [
        'nombre' => 'Hackeado',
        'precio' => 10000,
    ])->assertNotFound();

    expect($productoB->refresh()->nombre)->not->toBe('Hackeado');
});

test('crear un producto lo asocia al negocio actual', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/productos", [
        'nombre' => 'Cera para barba',
        'precio' => 15000,
        'visible' => true,
    ]);

    $respuesta->assertCreated();
    $this->assertDatabaseHas('productos', ['negocio_id' => $negocio->id, 'nombre' => 'Cera para barba']);
});

test('la respuesta de creacion refleja los valores por defecto de visible y orden, no null', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    // 'visible' y 'orden' se omiten a proposito: son opcionales, y antes
    // del fix la respuesta los devolvia en null aunque la base de datos
    // ya tuviera el default de la columna (true/0).
    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/productos", [
        'nombre' => 'Shampoo',
        'precio' => 18000,
    ]);

    $respuesta->assertCreated();
    expect($respuesta->json('data.visible'))->toBeTrue();
    expect($respuesta->json('data.orden'))->toBe(0);
});

test('actualizar un producto cambia sus datos', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Nombre viejo']);

    $respuesta = $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/productos/{$producto->id}", [
        'nombre' => 'Nombre nuevo',
        'precio' => 20000,
        'visible' => false,
    ]);

    $respuesta->assertOk();
    expect($producto->refresh()->nombre)->toBe('Nombre nuevo');
    expect($producto->visible)->toBeFalse();
});

test('eliminar un producto lo borra', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/productos/{$producto->id}");

    $respuesta->assertOk();
    expect(Producto::query()->find($producto->id))->toBeNull();
});

test('reordenar actualiza el campo orden de cada producto', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $primero = Producto::factory()->create(['negocio_id' => $negocio->id, 'orden' => 0]);
    $segundo = Producto::factory()->create(['negocio_id' => $negocio->id, 'orden' => 1]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/productos/reordenar", [
        'ids' => [$segundo->id, $primero->id],
    ]);

    $respuesta->assertOk();
    expect($segundo->refresh()->orden)->toBe(0);
    expect($primero->refresh()->orden)->toBe(1);
});
