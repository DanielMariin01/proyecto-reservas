<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Servicio;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('un usuario sin profesional vinculado no puede ver Mis citas de hoy', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/mis-citas-hoy")->assertForbidden();
    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/mis-citas-hoy")->assertForbidden();
});

test('el profesional ve solo sus citas de hoy, no las de otro dia ni las de otro profesional', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Corte']);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Ana Pérez']);

    $citaDeHoy = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->addDay()->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->addDay()->setTime(11, 0)->utc(),
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(14, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(15, 0)->utc(),
    ]);

    $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}/mis-citas-hoy")->assertOk();

    $respuesta = $this->actingAs($usuarioProfesional)->getJson("/app/{$negocio->slug}/api/mis-citas-hoy");

    $respuesta->assertOk();
    $citas = $respuesta->json('citas');
    expect($citas)->toHaveCount(1);
    expect($citas[0]['id'])->toBe($citaDeHoy->id);
    expect($citas[0]['clienteNombre'])->toBe('Ana Pérez');
});

test('marcar en atencion y despues completar con pago actualiza el estado y registra el pago', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
        'precio' => 30000,
    ]);

    $this->actingAs($usuarioProfesional)
        ->postJson("/app/{$negocio->slug}/api/mis-citas-hoy/citas/{$cita->id}/marcar-en-atencion")
        ->assertOk();

    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::EnAtencion);

    $respuesta = $this->actingAs($usuarioProfesional)->postJson(
        "/app/{$negocio->slug}/api/mis-citas-hoy/citas/{$cita->id}/completar-con-pago",
        ['metodo' => 'nequi', 'valor' => 30000, 'propina' => 2000],
    );

    $respuesta->assertOk();
    $cita->refresh();
    expect($cita->estado)->toBe(EstadoCitaValor::Completada);

    $pago = $cita->pagoActivo();
    expect($pago)->not->toBeNull();
    expect($pago->valor)->toBe(30000);
    expect($pago->propina)->toBe(2000);
});

test('un profesional no puede cambiar el estado de la cita de otro profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'))
        ->update(['usuario_id' => $usuarioProfesional->id]);

    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaAjena = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    $this->actingAs($usuarioProfesional)
        ->postJson("/app/{$negocio->slug}/api/mis-citas-hoy/citas/{$citaAjena->id}/marcar-en-atencion")
        ->assertNotFound();

    expect($citaAjena->refresh()->estado)->toBe(EstadoCitaValor::Confirmada);
});

test('marcar no asistio cambia el estado', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    $this->actingAs($usuarioProfesional)
        ->postJson("/app/{$negocio->slug}/api/mis-citas-hoy/citas/{$cita->id}/marcar-no-asistio")
        ->assertOk();

    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::NoAsistio);
});
