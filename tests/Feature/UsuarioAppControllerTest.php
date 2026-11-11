<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('un profesional no puede ver las paginas de usuarios del negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno, ['email' => 'dueno@ejemplo.com']);

    $this->actingAs($profesional)->get("/app/{$negocio->slug}/usuarios")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/usuarios/create")->assertForbidden();
    $this->actingAs($profesional)->get("/app/{$negocio->slug}/usuarios/{$dueno->id}/edit")->assertForbidden();
});

test('el dueno ve las 3 pantallas de usuarios', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion, ['email' => 'recepcion@ejemplo.com', 'nombre' => 'Recepción Uno']);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/usuarios")->assertOk()->assertSee('Usuarios');
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/usuarios/create")->assertOk();
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/usuarios/{$recepcion->id}/edit")->assertOk();
});

test('la api lista solo los usuarios del negocio actual', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    crearUsuarioConRol($negocioB, RolNegocio::Dueno, ['email' => 'otro@ejemplo.com']);

    $respuesta = $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/usuarios");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['email'])->toBe($duenoA->email);
});

test('un dueno no puede editar ni eliminar un usuario de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $staffB = crearUsuarioConRol($negocioB, RolNegocio::Recepcion, ['email' => 'staffb@ejemplo.com', 'nombre' => 'Original']);

    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/usuarios/{$staffB->id}/edit")->assertForbidden();
    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/usuarios/{$staffB->id}", [
        'nombre' => 'Hackeado',
        'telefono' => '3000000000',
        'rol' => 'dueno',
    ])->assertForbidden();
    $this->actingAs($duenoA)->deleteJson("/app/{$negocioA->slug}/api/usuarios/{$staffB->id}")->assertForbidden();

    expect($staffB->refresh()->nombre)->toBe('Original');
});

test('invitar un usuario le asigna el rol y le envia el correo de restablecimiento', function (): void {
    Notification::fake();
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/usuarios", [
        'nombre' => 'Nueva Recepcionista',
        'email' => 'nueva@ejemplo.com',
        'rol' => 'recepcion',
    ]);

    $respuesta->assertCreated();
    expect($respuesta->json('data.rol'))->toBe('recepcion');

    $usuario = Usuario::query()->where('email', 'nueva@ejemplo.com')->firstOrFail();
    Notification::assertSentTo($usuario, ResetPassword::class);
});

test('actualizar un usuario cambia sus datos y su rol', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $staff = crearUsuarioConRol($negocio, RolNegocio::Profesional, ['email' => 'staff@ejemplo.com', 'nombre' => 'Nombre viejo']);

    $respuesta = $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/usuarios/{$staff->id}", [
        'nombre' => 'Nombre nuevo',
        'telefono' => '3001112222',
        'rol' => 'recepcion',
    ]);

    $respuesta->assertOk();
    expect($respuesta->json('data.rol'))->toBe('recepcion');
    expect($staff->refresh()->nombre)->toBe('Nombre nuevo');
});

test('un dueno no puede eliminarse a si mismo', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/usuarios/{$dueno->id}");

    $respuesta->assertForbidden();
    expect(Usuario::query()->find($dueno->id))->not->toBeNull();
});

test('eliminar a otro usuario del negocio lo borra', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $staff = crearUsuarioConRol($negocio, RolNegocio::Recepcion, ['email' => 'staff2@ejemplo.com']);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/usuarios/{$staff->id}");

    $respuesta->assertOk();
    expect(Usuario::query()->find($staff->id))->toBeNull();
});
