<?php

declare(strict_types=1);

use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;
use App\Models\PagoSuscripcion;
use App\Models\PaletaMarca;
use App\Models\RegistroAuditoria;
use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('un usuario normal no puede ver las paginas de negocios', function (): void {
    $usuario = Usuario::factory()->create();
    $negocio = Negocio::factory()->create();

    $this->actingAs($usuario)->get('/admin/negocios')->assertForbidden();
    $this->actingAs($usuario)->get('/admin/negocios/create')->assertForbidden();
    $this->actingAs($usuario)->get("/admin/negocios/{$negocio->slug}")->assertForbidden();
    $this->actingAs($usuario)->get("/admin/negocios/{$negocio->slug}/edit")->assertForbidden();
});

test('el super admin ve las 4 pantallas de negocios', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create(['nombre' => 'Negocio Ejemplo']);

    $this->actingAs($superAdmin)->get('/admin/negocios')->assertOk()->assertSee('Negocios');
    $this->actingAs($superAdmin)->get('/admin/negocios/create')->assertOk();
    $this->actingAs($superAdmin)->get("/admin/negocios/{$negocio->slug}")->assertOk();
    $this->actingAs($superAdmin)->get("/admin/negocios/{$negocio->slug}/edit")->assertOk();
});

test('la api lista los negocios ordenados por nombre', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    Negocio::factory()->create(['nombre' => 'Zeta']);
    Negocio::factory()->create(['nombre' => 'Alfa']);

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/negocios');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos[0]['nombre'])->toBe('Alfa');
});

test('la api muestra el detalle de un negocio con sus usuarios', function (): void {
    Notification::fake();
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->getJson("/admin/api/negocios/{$negocio->slug}");

    $respuesta->assertOk();
    expect($respuesta->json('data.slug'))->toBe($negocio->slug);
    expect($respuesta->json('data.usuarios'))->toBeArray();
});

test('crear un negocio crea el dueno y le envia el correo de restablecimiento', function (): void {
    Notification::fake();
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson('/admin/api/negocios', [
        'nombre' => 'Barbería Nueva',
        'tipo' => 'barberia',
        'zona_horaria' => 'America/Bogota',
        'direccion' => 'Calle 1',
        'ciudad' => 'Bogotá',
        'telefono' => '3001234567',
        'whatsapp' => '3001234567',
        'paleta_marca_id' => null,
        'permite_editar_pagina' => false,
        'slug' => null,
        'nombre_dueno' => 'Dueño Nuevo',
        'email_dueno' => 'dueno@ejemplo.com',
    ]);

    $respuesta->assertCreated();

    $negocio = Negocio::query()->where('nombre', 'Barbería Nueva')->firstOrFail();
    $dueno = Usuario::query()->where('email', 'dueno@ejemplo.com')->firstOrFail();

    expect($negocio->duenos()->pluck('id'))->toContain($dueno->id);
    Notification::assertSentTo($dueno, ResetPassword::class);
});

test('actualizar un negocio cambia sus datos sin tocar el dueno', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $paleta = PaletaMarca::factory()->create();
    $negocio = Negocio::factory()->create(['nombre' => 'Nombre Viejo']);

    $respuesta = $this->actingAs($superAdmin)->putJson("/admin/api/negocios/{$negocio->slug}", [
        'nombre' => 'Nombre Nuevo',
        'tipo' => 'spa',
        'zona_horaria' => 'America/Bogota',
        'direccion' => 'Nueva dirección',
        'ciudad' => 'Medellín',
        'telefono' => '3009999999',
        'whatsapp' => '3009999999',
        'paleta_marca_id' => $paleta->id,
        'permite_editar_pagina' => true,
    ]);

    $respuesta->assertOk();
    $negocio->refresh();

    expect($negocio->nombre)->toBe('Nombre Nuevo');
    expect($negocio->paleta_marca_id)->toBe($paleta->id);
    expect($negocio->permite_editar_pagina)->toBeTrue();
});

test('eliminar un negocio lo borra', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->deleteJson("/admin/api/negocios/{$negocio->slug}");

    $respuesta->assertOk();
    expect(Negocio::query()->find($negocio->id))->toBeNull();
});

test('entrar como el negocio fija la suplantacion y devuelve la url del panel', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/negocios/{$negocio->slug}/entrar");

    $respuesta->assertOk();
    expect($respuesta->json('url'))->toContain($negocio->slug);
    expect(session('negocio_suplantado_id'))->toBe($negocio->id);
});

test('registrar un pago actualiza la proxima fecha de pago y reactiva el negocio', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->vencido()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/negocios/{$negocio->slug}/registrar-pago", [
        'fecha' => now()->toDateString(),
        'valor' => 35000,
        'metodo' => 'nequi',
        'periodo_desde' => now()->toDateString(),
        'periodo_hasta' => now()->addMonth()->toDateString(),
    ]);

    $respuesta->assertOk();
    $negocio->refresh();

    expect($negocio->estado_suscripcion)->toBe(EstadoSuscripcion::Activo);
    expect(PagoSuscripcion::query()->where('negocio_id', $negocio->id)->count())->toBe(1);
});

test('cambiar el estado a suspendido deja de permitir el acceso al panel', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/negocios/{$negocio->slug}/cambiar-estado", [
        'estado_destino' => 'suspendido',
        'motivo' => 'Falta de pago',
    ]);

    $respuesta->assertOk();
    $negocio->refresh();

    expect($negocio->estado_suscripcion)->toBe(EstadoSuscripcion::Suspendido);
    expect($negocio->estaOperativo())->toBeFalse();
});

test('la api lista los pagos de suscripcion de un negocio', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();
    PagoSuscripcion::factory()->create(['negocio_id' => $negocio->id]);
    PagoSuscripcion::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->getJson("/admin/api/negocios/{$negocio->slug}/pagos-suscripcion");

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(1);
});

test('el registro de auditoria filtrado por negocio solo muestra los suyos', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();
    RegistroAuditoria::create(['negocio_id' => $negocio->id, 'accion' => 'negocio.creado', 'hecho_por_soporte' => false]);
    RegistroAuditoria::create(['negocio_id' => Negocio::factory()->create()->id, 'accion' => 'negocio.creado', 'hecho_por_soporte' => false]);

    $respuesta = $this->actingAs($superAdmin)->getJson("/admin/api/registro-auditorias?negocioId={$negocio->id}");

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(1);
});
