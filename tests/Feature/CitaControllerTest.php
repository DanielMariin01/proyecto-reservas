<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\DiaSemana;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;

/**
 * @return array{negocio: Negocio, profesional: Profesional, servicio: Servicio, fecha: CarbonImmutable}
 */
function negocioListoParaCrearCitaDesdeElPanel(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-01 00:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
        'visible' => true,
    ]);
    $servicio->profesionales()->attach($profesional->id);

    $fecha = CarbonImmutable::now('America/Bogota')->next(CarbonImmutable::TUESDAY);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('el dueno puede ver la lista de citas', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/citas")->assertOk();
    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/citas")->assertOk();
});

test('un profesional no puede crear citas desde el panel', function (): void {
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/citas/create")->assertForbidden();
    $this->actingAs($profesionalUsuario)->postJson("/app/{$negocio->slug}/api/citas", [
        'servicio_id' => 1,
        'fecha' => '2026-11-10',
        'hora' => '10:00',
        'nombre_cliente' => 'Ana',
        'celular_cliente' => '3001234567',
    ])->assertForbidden();
});

test('el dueno puede crear una cita desde el panel con un profesional especifico', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioListoParaCrearCitaDesdeElPanel();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/citas", [
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'fecha' => $fecha->toDateString(),
        'hora' => '10:00',
        'nombre_cliente' => 'Ana',
        'celular_cliente' => '3001234567',
        'correo_cliente' => 'ana@example.com',
    ]);

    $respuesta->assertCreated();

    $cita = Cita::query()->where('profesional_id', $profesional->id)->first();
    expect($cita)->not->toBeNull();
    expect(Cliente::query()->where('celular', '3001234567')->count())->toBe(1);
});

test('el dueno puede crear una cita con cualquier profesional disponible', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicio, 'fecha' => $fecha] = negocioListoParaCrearCitaDesdeElPanel();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/citas", [
        'servicio_id' => $servicio->id,
        'fecha' => $fecha->toDateString(),
        'hora' => '10:00',
        'nombre_cliente' => 'Ana',
        'celular_cliente' => '3001234567',
    ]);

    $respuesta->assertCreated();
    expect(Cita::query()->count())->toBe(1);
});

test('crear una cita con una hora ya tomada devuelve un error y no cambia nada', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioListoParaCrearCitaDesdeElPanel();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
        'inicio' => CarbonImmutable::parse("{$fecha->toDateString()} 10:00", 'America/Bogota')->utc(),
        'fin' => CarbonImmutable::parse("{$fecha->toDateString()} 11:00", 'America/Bogota')->utc(),
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/citas", [
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'fecha' => $fecha->toDateString(),
        'hora' => '10:00',
        'nombre_cliente' => 'Ana',
        'celular_cliente' => '3001234567',
    ]);

    $respuesta->assertStatus(422);
    expect($respuesta->json('mensaje'))->not->toBeNull();
    expect(Cita::query()->count())->toBe(1);
});

test('un profesional solo ve sus propias citas en la lista', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesionalPropio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesionalPropio->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaPropia = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesionalPropio->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $otroProfesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);

    $respuesta = $this->actingAs($usuarioProfesional)->getJson("/app/{$negocio->slug}/api/citas");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');
    expect($datos)->toHaveCount(1);
    expect($datos[0]['id'])->toBe($citaPropia->id);
});

test('el dueno ve todas las citas del negocio y puede filtrar por profesional y estado', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesionalA = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'A'));
    $profesionalB = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'B'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesionalA->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesionalB->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Cancelada,
    ]);

    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/citas")->assertOk()->assertJsonCount(2, 'data');

    $respuestaFiltrada = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/citas?profesional_id={$profesionalA->id}");
    $respuestaFiltrada->assertOk()->assertJsonCount(1, 'data');
    expect($respuestaFiltrada->json('data.0.profesionalNombre'))->toBe('A');

    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/citas?estado=cancelada")->assertOk()->assertJsonCount(1, 'data');
});

test('la lista de citas no muestra citas de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $profesionalB = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocioB, nombre: 'Ajeno'));
    $servicioB = Servicio::factory()->create(['negocio_id' => $negocioB->id]);
    $clienteB = Cliente::factory()->create(['negocio_id' => $negocioB->id]);
    Cita::factory()->create([
        'negocio_id' => $negocioB->id, 'profesional_id' => $profesionalB->id, 'servicio_id' => $servicioB->id, 'cliente_id' => $clienteB->id,
    ]);

    $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/citas")->assertOk()->assertJsonCount(0, 'data');
});

test('el dueno puede cambiar el estado de una cita desde la lista general (mismo endpoint que la agenda)', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Pendiente,
    ]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/citas/{$cita->id}/cambiar-estado", [
        'estado' => 'confirmada',
    ]);

    $respuesta->assertOk();
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Confirmada);
});
