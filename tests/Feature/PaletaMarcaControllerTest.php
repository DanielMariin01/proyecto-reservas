<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Models\PaletaMarca;
use App\Models\Usuario;

test('un usuario normal no puede ver las paginas de paletas de marca', function (): void {
    $usuario = Usuario::factory()->create();
    $paleta = PaletaMarca::factory()->create();

    $this->actingAs($usuario)->get('/admin/paleta-marcas')->assertForbidden();
    $this->actingAs($usuario)->get('/admin/paleta-marcas/create')->assertForbidden();
    $this->actingAs($usuario)->get("/admin/paleta-marcas/{$paleta->id}/edit")->assertForbidden();
});

test('el super admin ve las 3 pantallas de paletas de marca', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $paleta = PaletaMarca::factory()->create(['nombre' => 'Color Ejemplo']);

    $this->actingAs($superAdmin)->get('/admin/paleta-marcas')->assertOk()->assertSee('Paletas de marca');
    $this->actingAs($superAdmin)->get('/admin/paleta-marcas/create')->assertOk();
    $this->actingAs($superAdmin)->get("/admin/paleta-marcas/{$paleta->id}/edit")
        ->assertOk()
        ->assertSee('Color Ejemplo', escape: false);
});

test('la api lista las paletas ordenadas por nombre con el conteo de negocios', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $paleta = PaletaMarca::factory()->create(['nombre' => 'Alfa']);
    Negocio::factory()->create(['paleta_marca_id' => $paleta->id]);
    PaletaMarca::factory()->create(['nombre' => 'Zeta']);

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/paleta-marcas');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos[0]['nombre'])->toBe('Alfa');
    expect($datos[0]['negociosCount'])->toBe(1);
});

test('la api filtra por nombre al buscar', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    PaletaMarca::factory()->create(['nombre' => 'Color Spa']);
    $barberia = PaletaMarca::factory()->create(['nombre' => 'Color Barbería']);

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/paleta-marcas?buscar=Barber');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['id'])->toBe($barberia->id);
});

test('actualizar una paleta con un nombre repetido falla la validacion', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    PaletaMarca::factory()->create(['nombre' => 'Color Spa']);
    $paleta = PaletaMarca::factory()->create(['nombre' => 'Color Uñas']);

    $respuesta = $this->actingAs($superAdmin)->putJson("/admin/api/paleta-marcas/{$paleta->id}", [
        'nombre' => 'Color Spa',
        'color_primario' => '#1B263B',
        'color_secundario' => '#415A77',
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['nombre']);
});

test('actualizar una paleta con su propio nombre no falla por unicidad', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $paleta = PaletaMarca::factory()->create(['nombre' => 'Color Uñas']);

    $respuesta = $this->actingAs($superAdmin)->putJson("/admin/api/paleta-marcas/{$paleta->id}", [
        'nombre' => 'Color Uñas',
        'color_primario' => '#1B263B',
        'color_secundario' => '#000000',
    ]);

    $respuesta->assertOk();
    expect($paleta->refresh()->color_secundario)->toBe('#000000');
});

test('eliminar una paleta en uso deja el negocio sin paleta en vez de bloquear', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $paleta = PaletaMarca::factory()->create();
    $negocio = Negocio::factory()->create(['paleta_marca_id' => $paleta->id]);

    $respuesta = $this->actingAs($superAdmin)->deleteJson("/admin/api/paleta-marcas/{$paleta->id}");

    $respuesta->assertOk();
    expect(PaletaMarca::query()->find($paleta->id))->toBeNull();
    expect($negocio->refresh()->paleta_marca_id)->toBeNull();
});
