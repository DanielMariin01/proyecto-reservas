<?php

declare(strict_types=1);

use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('al crear un usuario se le envia un correo para elegir contrasena', function (): void {
    Notification::fake();

    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson('/admin/api/usuarios', [
        'nombre' => 'Nuevo Usuario',
        'email' => 'nuevo@ejemplo.com',
        'telefono' => '3001234567',
        'es_super_admin' => false,
    ]);

    $respuesta->assertCreated();

    $usuario = Usuario::query()->where('email', 'nuevo@ejemplo.com')->firstOrFail();

    Notification::assertSentTo($usuario, ResetPassword::class);
});

test('restablecer la contrasena de un usuario le envia el correo de nuevo', function (): void {
    Notification::fake();

    $superAdmin = Usuario::factory()->superAdmin()->create();
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/usuarios/{$usuario->id}/restablecer-contrasena");

    $respuesta->assertOk();
    Notification::assertSentTo($usuario, ResetPassword::class);
});
