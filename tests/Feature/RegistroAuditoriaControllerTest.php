<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Models\RegistroAuditoria;
use App\Models\Usuario;

test('un usuario normal no puede ver la pagina de registro de auditoria', function (): void {
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($usuario)->get('/admin/registro-auditorias');

    $respuesta->assertForbidden();
});

test('el super admin ve la pagina de registro de auditoria', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->get('/admin/registro-auditorias');

    $respuesta->assertOk()->assertSee('Registro de auditoría');
});

test('la api devuelve los registros ordenados por fecha descendente', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();

    $antiguo = RegistroAuditoria::create([
        'negocio_id' => $negocio->id,
        'accion' => 'creo_negocio',
        'hecho_por_soporte' => false,
    ]);
    $antiguo->created_at = now()->subDay();
    $antiguo->save();

    $reciente = RegistroAuditoria::create([
        'negocio_id' => $negocio->id,
        'accion' => 'suspendio_negocio',
        'hecho_por_soporte' => true,
        'motivo' => 'Pago vencido',
    ]);

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/registro-auditorias');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos[0]['id'])->toBe($reciente->id);
    expect($datos[0]['hechoPorSoporte'])->toBeTrue();
    expect($datos[0]['motivo'])->toBe('Pago vencido');
    expect($datos[1]['id'])->toBe($antiguo->id);
});

test('la api filtra por accion al buscar', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    RegistroAuditoria::create(['accion' => 'creo_negocio', 'hecho_por_soporte' => false]);
    $suspendido = RegistroAuditoria::create(['accion' => 'suspendio_negocio', 'hecho_por_soporte' => false]);

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/registro-auditorias?buscar=suspendio');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['id'])->toBe($suspendido->id);
});
