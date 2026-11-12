<?php

declare(strict_types=1);

use App\Enums\DiaSemana;
use App\Enums\RolNegocio;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;

test('un profesional sin permiso de gestionar catalogo no puede ver ni editar el horario', function (): void {
    $negocio = Negocio::factory()->create();
    $usuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $datosHorario = ['dias' => [DiaSemana::Lunes->value => ['disponible' => false, 'rangos' => []]]];

    $this->actingAs($usuario)->getJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/horario")->assertForbidden();
    $this->actingAs($usuario)->putJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/horario", $datosHorario)->assertForbidden();
});

test('el dueno obtiene el horario agrupado por dia', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::Lunes,
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/horario");

    $respuesta->assertOk();
    expect($respuesta->json('dias.'.DiaSemana::Lunes->value.'.disponible'))->toBeTrue();
    expect($respuesta->json('dias.'.DiaSemana::Lunes->value.'.rangos'))->toHaveCount(1);
    expect($respuesta->json('dias.'.DiaSemana::Martes->value.'.disponible'))->toBeFalse();
});

test('el dueno guarda el horario semanal de un profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/horario", [
        'dias' => [
            DiaSemana::Lunes->value => [
                'disponible' => true,
                'rangos' => [
                    ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                    ['hora_inicio' => '14:00', 'hora_fin' => '18:00'],
                ],
            ],
            DiaSemana::Martes->value => ['disponible' => false, 'rangos' => []],
        ],
    ]);

    $respuesta->assertOk();
    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(2);
});

test('guardar rangos superpuestos devuelve un error y no cambia nada', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/profesionales/{$profesional->id}/horario", [
        'dias' => [
            DiaSemana::Lunes->value => [
                'disponible' => true,
                'rangos' => [
                    ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                    ['hora_inicio' => '11:00', 'hora_fin' => '13:00'],
                ],
            ],
        ],
    ]);

    $respuesta->assertStatus(422);
    expect($respuesta->json('mensaje'))->not->toBeNull();
    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(0);
});

test('un dueno no puede ver ni editar el horario de un profesional de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $profesionalB = Profesional::factory()->create(['negocio_id' => $negocioB->id]);

    $datosHorario = ['dias' => [DiaSemana::Lunes->value => ['disponible' => false, 'rangos' => []]]];

    $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/profesionales/{$profesionalB->id}/horario")->assertNotFound();
    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/profesionales/{$profesionalB->id}/horario", $datosHorario)->assertNotFound();
});

test('la vista previa de disponibilidad del dueno devuelve las franjas del profesional pedido', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($dueno)->getJson(
        "/app/{$negocio->slug}/api/profesionales/{$profesional->id}/vista-previa-disponibilidad?servicio_id={$servicio->id}&fecha=2026-11-12",
    );

    $respuesta->assertOk();
    expect($respuesta->json('franjas'))->toBeArray();
});
