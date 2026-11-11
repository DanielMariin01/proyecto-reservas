<?php

declare(strict_types=1);

use App\Models\Usuario;

test('un usuario normal no puede ver las paginas de usuarios', function (): void {
    $usuario = Usuario::factory()->create();
    $otro = Usuario::factory()->create();

    $this->actingAs($usuario)->get('/admin/usuarios')->assertForbidden();
    $this->actingAs($usuario)->get('/admin/usuarios/create')->assertForbidden();
    $this->actingAs($usuario)->get("/admin/usuarios/{$otro->id}/edit")->assertForbidden();
});

test('el super admin ve las 3 pantallas de usuarios', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $usuario = Usuario::factory()->create(['nombre' => 'Usuario Ejemplo']);

    $this->actingAs($superAdmin)->get('/admin/usuarios')->assertOk()->assertSee('Usuarios');
    $this->actingAs($superAdmin)->get('/admin/usuarios/create')->assertOk();
    $this->actingAs($superAdmin)->get("/admin/usuarios/{$usuario->id}/edit")
        ->assertOk()
        ->assertSee('Usuario Ejemplo', escape: false);
});

test('la api lista los usuarios ordenados por nombre', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create(['nombre' => 'Super Admin']);
    Usuario::factory()->create(['nombre' => 'Zeta']);
    Usuario::factory()->create(['nombre' => 'Alfa']);

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/usuarios');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos[0]['nombre'])->toBe('Alfa');
});

test('actualizar un usuario con un correo repetido falla la validacion', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    Usuario::factory()->create(['email' => 'ocupado@ejemplo.com']);
    $usuario = Usuario::factory()->create(['email' => 'libre@ejemplo.com']);

    $respuesta = $this->actingAs($superAdmin)->putJson("/admin/api/usuarios/{$usuario->id}", [
        'nombre' => $usuario->nombre,
        'email' => 'ocupado@ejemplo.com',
        'telefono' => '3000000000',
        'es_super_admin' => false,
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['email']);
});

test('actualizar un usuario con su propio correo no falla por unicidad', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $usuario = Usuario::factory()->create(['email' => 'libre@ejemplo.com']);

    $respuesta = $this->actingAs($superAdmin)->putJson("/admin/api/usuarios/{$usuario->id}", [
        'nombre' => 'Nombre Actualizado',
        'email' => 'libre@ejemplo.com',
        'telefono' => '3000000000',
        'es_super_admin' => true,
    ]);

    $respuesta->assertOk();
    expect($usuario->refresh()->nombre)->toBe('Nombre Actualizado');
    expect($usuario->refresh()->es_super_admin)->toBeTrue();
});

test('eliminar un usuario lo borra', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->deleteJson("/admin/api/usuarios/{$usuario->id}");

    $respuesta->assertOk();
    expect(Usuario::query()->find($usuario->id))->toBeNull();
});
