<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Enums\TipoBloqueo;
use App\Models\Bloqueo;
use App\Models\Negocio;
use App\Models\Profesional;

test('un usuario normal no puede ver ni modificar los bloqueos', function (): void {
    $negocio = Negocio::factory()->create();
    $usuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($usuario)->getJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/bloqueos")->assertForbidden();
    $this->actingAs($usuario)->postJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/bloqueos", [
        'tipo' => 'bloqueo',
        'fecha_inicio' => '2026-11-10',
        'hora_inicio' => '09:00',
        'fecha_fin' => '2026-11-10',
        'hora_fin' => '10:00',
    ])->assertForbidden();
});

test('crear un bloqueo convierte la fecha y hora locales a utc', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/bloqueos", [
        'tipo' => 'bloqueo',
        'fecha_inicio' => '2026-11-10',
        'hora_inicio' => '09:00',
        'fecha_fin' => '2026-11-10',
        'hora_fin' => '10:00',
        'motivo' => 'Cita médica',
    ]);

    $respuesta->assertCreated();

    $bloqueo = Bloqueo::query()->where('profesional_id', $profesional->id)->firstOrFail();
    expect($bloqueo->tipo)->toBe(TipoBloqueo::Bloqueo);
    expect($bloqueo->inicio->toDateTimeString())->toBe('2026-11-10 14:00:00');
    expect($bloqueo->fin->toDateTimeString())->toBe('2026-11-10 15:00:00');
});

test('la api lista solo los bloqueos del profesional pedido', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesionalUno = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $profesionalDos = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    Bloqueo::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesionalUno->id]);
    Bloqueo::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesionalDos->id]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/profesionales/{$profesionalUno->id}/bloqueos");

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(1);
});

test('un dueno no puede modificar el bloqueo de un profesional de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $profesionalB = Profesional::factory()->create(['negocio_id' => $negocioB->id]);
    $bloqueoB = Bloqueo::factory()->create(['negocio_id' => $negocioB->id, 'profesional_id' => $profesionalB->id]);

    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/profesionales/{$profesionalB->id}/bloqueos/{$bloqueoB->id}", [
        'tipo' => 'bloqueo',
        'fecha_inicio' => '2026-11-10',
        'hora_inicio' => '09:00',
        'fecha_fin' => '2026-11-10',
        'hora_fin' => '10:00',
    ])->assertNotFound();
});

test('eliminar un bloqueo lo borra', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $bloqueo = Bloqueo::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id]);

    $respuesta = $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/bloqueos/{$bloqueo->id}");

    $respuesta->assertOk();
    expect(Bloqueo::query()->find($bloqueo->id))->toBeNull();
});
