<?php

declare(strict_types=1);

use App\Models\Usuario;

test('el super admin ve el panel admin', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->get('/admin');

    $respuesta->assertOk()->assertSee('Panel admin');
});

test('un usuario que no es super admin recibe 403 en el panel admin', function (): void {
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($usuario)->get('/admin');

    $respuesta->assertForbidden();
});

test('sin sesion se redirige a ingresar', function (): void {
    $respuesta = $this->get('/admin');

    $respuesta->assertRedirect(route('login'));
});
