<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;

test('un profesional (usuario con ese rol) no puede ver las paginas de profesionales', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}/profesionales")->assertForbidden();
    $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}/profesionales/create")->assertForbidden();
    $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}/profesionales/{$profesional->id}")->assertForbidden();
    $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}/profesionales/{$profesional->id}/edit")->assertForbidden();
});

test('el dueno ve las 4 pantallas de profesionales', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Japo']);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/profesionales")->assertOk()->assertSee('Profesionales');
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/profesionales/create")->assertOk();
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/profesionales/{$profesional->id}")->assertOk();
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/profesionales/{$profesional->id}/edit")->assertOk();
});

test('la api lista solo los profesionales del negocio actual', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    Profesional::factory()->create(['negocio_id' => $negocioA->id, 'nombre' => 'De A']);
    Profesional::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'De B']);

    $respuesta = $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/profesionales");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['nombre'])->toBe('De A');
});

test('un dueno no puede editar ni eliminar un profesional de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $profesionalB = Profesional::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Original']);

    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/profesionales/{$profesionalB->id}")->assertNotFound();
    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/profesionales/{$profesionalB->id}/edit")->assertNotFound();
    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/profesionales/{$profesionalB->id}", [
        'nombre' => 'Hackeado',
        'especialidad' => '',
        'biografia' => '',
        'certificaciones' => '',
        'color' => '#000000',
        'activo' => true,
    ])->assertNotFound();
    $this->actingAs($duenoA)->deleteJson("/app/{$negocioA->slug}/api/profesionales/{$profesionalB->id}")->assertNotFound();

    expect($profesionalB->refresh()->nombre)->toBe('Original');
});

test('crear un profesional lo asocia al negocio actual', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/profesionales", [
        'nombre' => 'Nuevo Profesional',
        'especialidad' => 'Barbero',
    ]);

    $respuesta->assertCreated();
    $this->assertDatabaseHas('profesionales', ['negocio_id' => $negocio->id, 'nombre' => 'Nuevo Profesional']);
});

test('crear un profesional con usuario vinculado envia el correo de restablecimiento', function (): void {
    Notification::fake();
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/profesionales", [
        'nombre' => 'Nuevo Profesional',
        'nombre_usuario' => 'Nuevo Profesional',
        'email_usuario' => 'nuevoprofesional@ejemplo.com',
    ]);

    $respuesta->assertCreated();
    expect($respuesta->json('data.usuarioEmail'))->toBe('nuevoprofesional@ejemplo.com');

    $usuario = Usuario::query()->where('email', 'nuevoprofesional@ejemplo.com')->firstOrFail();
    Notification::assertSentTo($usuario, ResetPassword::class);
});

test('actualizar un profesional cambia sus datos', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Nombre viejo']);

    $respuesta = $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}", [
        'nombre' => 'Nombre nuevo',
        'especialidad' => 'Estilista',
        'activo' => false,
    ]);

    $respuesta->assertOk();
    expect($profesional->refresh()->nombre)->toBe('Nombre nuevo');
    expect($profesional->activo)->toBeFalse();
});

test('eliminar un profesional lo borra', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}");

    $respuesta->assertOk();
    expect(Profesional::query()->find($profesional->id))->toBeNull();
});

test('vincular un usuario a un profesional ya existente le asigna el rol profesional', function (): void {
    Notification::fake();
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/vincular-usuario", [
        'nombre' => 'Japo',
        'email' => 'japo@ejemplo.com',
    ]);

    $respuesta->assertOk();
    expect($profesional->refresh()->usuario_id)->not->toBeNull();

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);
    expect($profesional->usuario->hasRole(RolNegocio::Profesional->value))->toBeTrue();
});
