<?php

declare(strict_types=1);

use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('solicitar el enlace con un correo registrado envia la notificacion', function (): void {
    Notification::fake();

    $usuario = Usuario::factory()->create(['email' => 'cliente@ejemplo.com']);

    $respuesta = $this->post('/olvide-contrasena', ['email' => 'cliente@ejemplo.com']);

    $respuesta->assertRedirect();
    $respuesta->assertSessionHas('status');
    Notification::assertSentTo($usuario, ResetPassword::class);
});

test('solicitar el enlace con un correo que no existe responde igual, sin revelar nada', function (): void {
    Notification::fake();

    $respuesta = $this->post('/olvide-contrasena', ['email' => 'nadie@ejemplo.com']);

    $respuesta->assertRedirect();
    $respuesta->assertSessionHas('status');
    Notification::assertNothingSent();
});

test('la pantalla de solicitud pide un correo valido', function (): void {
    $respuesta = $this->post('/olvide-contrasena', ['email' => 'no-es-un-correo']);

    $respuesta->assertSessionHasErrors('email');
});

test('la pantalla de solicitud se puede ver sin sesion', function (): void {
    $this->get('/olvide-contrasena')->assertOk();
});
