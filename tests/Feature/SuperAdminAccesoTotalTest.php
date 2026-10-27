<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Models\Usuario;

test('el super admin ve el listado de negocios de cualquier negocio', function (): void {
    Negocio::factory()->count(3)->create();

    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->get('/admin/negocios');

    $respuesta->assertOk();
});

test('el super admin ve el listado global de usuarios', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->get('/admin/usuarios');

    $respuesta->assertOk();
});

test('un usuario que no es super admin no puede entrar al panel admin', function (): void {
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($usuario)->get('/admin');

    $respuesta->assertForbidden();
});
