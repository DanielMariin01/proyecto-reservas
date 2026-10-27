<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Spatie\Permission\PermissionRegistrar;

test('un usuario con un solo negocio entra directo a su panel', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno, ['password' => bcrypt('password')]);

    $respuesta = $this->post('/ingresar', [
        'email' => $dueno->email,
        'password' => 'password',
    ]);

    $respuesta->assertRedirect("/app/{$negocio->slug}");
});

test('un super admin entra directo al panel admin', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create(['password' => bcrypt('password')]);

    $respuesta = $this->post('/ingresar', [
        'email' => $superAdmin->email,
        'password' => 'password',
    ]);

    $respuesta->assertRedirect('/admin');
});

test('un usuario con varios negocios va a la pantalla de elegir negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();

    $usuario = Usuario::factory()->create(['password' => bcrypt('password')]);

    foreach ([$negocioA, $negocioB] as $negocio) {
        app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);
        $usuario->assignRole(RolNegocio::Dueno->value);
    }

    $respuesta = $this->post('/ingresar', [
        'email' => $usuario->email,
        'password' => 'password',
    ]);

    $respuesta->assertRedirect(route('elegir-negocio'));

    $respuestaChooser = $this->actingAs($usuario)->get(route('elegir-negocio'));
    $respuestaChooser->assertSee($negocioA->nombre);
    $respuestaChooser->assertSee($negocioB->nombre);
});

test('credenciales invalidas no inician sesion', function (): void {
    $usuario = Usuario::factory()->create(['password' => bcrypt('password')]);

    $respuesta = $this->post('/ingresar', [
        'email' => $usuario->email,
        'password' => 'incorrecta',
    ]);

    $respuesta->assertSessionHasErrors('email');
    $this->assertGuest();
});
