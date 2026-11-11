<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Servicio;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('un profesional no puede ver la agenda general', function (): void {
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/agenda")->assertForbidden();
    $this->actingAs($profesionalUsuario)->getJson("/app/{$negocio->slug}/api/agenda")->assertForbidden();
});

test('el dueno ve la agenda del dia agrupada por profesional', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Corte']);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Ana Pérez']);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/agenda")->assertOk();

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/agenda?fecha=2026-11-03");

    $respuesta->assertOk();
    expect($respuesta->json('columnas.0.profesionalNombre'))->toBe('Japo');
    expect($respuesta->json('columnas.0.citas.0.clienteNombre'))->toBe('Ana Pérez');
    expect($respuesta->json('columnas.0.citas.0.servicioNombre'))->toBe('Corte');
});

test('la agenda no muestra citas de otro negocio aunque coincida la fecha', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));

    $otroNegocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $profesionalOtro = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $otroNegocio, nombre: 'Ajeno'));
    $servicioOtro = Servicio::factory()->create(['negocio_id' => $otroNegocio->id]);
    $clienteOtro = Cliente::factory()->create(['negocio_id' => $otroNegocio->id]);
    Cita::factory()->create([
        'negocio_id' => $otroNegocio->id,
        'profesional_id' => $profesionalOtro->id,
        'servicio_id' => $servicioOtro->id,
        'cliente_id' => $clienteOtro->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/agenda?fecha=2026-11-03");

    $respuesta->assertOk();
    expect($respuesta->json('columnas.0.citas'))->toBe([]);
});

test('el dueno puede cancelar una cita desde la agenda', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Pendiente,
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/citas/{$cita->id}/cancelar", [
        'motivo' => 'El cliente no puede asistir',
    ]);

    $respuesta->assertOk();
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Cancelada);
});

test('el dueno puede completar una cita con pago desde la agenda', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::EnAtencion,
        'precio' => 30000,
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/citas/{$cita->id}/completar-con-pago", [
        'metodo' => 'efectivo',
        'valor' => 30000,
        'propina' => 0,
    ]);

    $respuesta->assertOk();
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Completada);
    expect($cita->pagoActivo()?->valor)->toBe(30000);
});

test('cambiar a un estado invalido devuelve un error y no cambia nada', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Cancelada,
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/citas/{$cita->id}/cambiar-estado", [
        'estado' => 'confirmada',
    ]);

    $respuesta->assertStatus(422);
    expect($respuesta->json('mensaje'))->not->toBeNull();
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Cancelada);
});

test('el dueno puede anular el pago de una cita completada', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Completada,
    ]);
    $pago = Pago::factory()->create(['negocio_id' => $negocio->id, 'cita_id' => $cita->id]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/citas/{$cita->id}/anular-pago", [
        'motivo' => 'Se registró dos veces',
    ]);

    $respuesta->assertOk();
    expect($pago->refresh()->anulado_en)->not->toBeNull();
});

test('un dueno no puede actuar sobre la cita de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $profesionalB = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocioB, nombre: 'Ajeno'));
    $servicioB = Servicio::factory()->create(['negocio_id' => $negocioB->id]);
    $clienteB = Cliente::factory()->create(['negocio_id' => $negocioB->id]);
    $citaB = Cita::factory()->create([
        'negocio_id' => $negocioB->id,
        'profesional_id' => $profesionalB->id,
        'servicio_id' => $servicioB->id,
        'cliente_id' => $clienteB->id,
        'estado' => EstadoCitaValor::Pendiente,
    ]);

    $this->actingAs($duenoA)->postJson("/app/{$negocioA->slug}/api/citas/{$citaB->id}/cancelar", [
        'motivo' => 'intento ajeno',
    ])->assertNotFound();

    expect($citaB->refresh()->estado)->toBe(EstadoCitaValor::Pendiente);
});
