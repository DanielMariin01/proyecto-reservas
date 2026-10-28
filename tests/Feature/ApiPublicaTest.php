<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\DiaSemana;
use App\Enums\EstadoSuscripcion;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Support\Fecha;
use Carbon\CarbonImmutable;

/**
 * Negocio con un profesional activo que presta un servicio visible, con
 * horario todos los martes 10:00-12:00 (hora Bogotá), y "ahora" bastante
 * antes para que el aviso mínimo nunca interfiera.
 *
 * @return array{negocio: Negocio, profesional: Profesional, servicio: Servicio, fecha: CarbonImmutable}
 */
function negocioPublicoListo(): array
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

test('GET servicios solo devuelve los visibles', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicioVisible] = negocioPublicoListo();
    $servicioOculto = Servicio::factory()->create(['negocio_id' => $negocio->id, 'visible' => false]);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/servicios");

    $respuesta->assertOk();
    $ids = collect($respuesta->json('data'))->pluck('id');
    expect($ids)->toContain($servicioVisible->id);
    expect($ids)->not->toContain($servicioOculto->id);
});

test('GET profesionales solo devuelve los activos', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesionalActivo] = negocioPublicoListo();
    $profesionalInactivo = Profesional::factory()->inactivo()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/profesionales");

    $respuesta->assertOk();
    $ids = collect($respuesta->json('data'))->pluck('id');
    expect($ids)->toContain($profesionalActivo->id);
    expect($ids)->not->toContain($profesionalInactivo->id);
});

test('GET disponibilidad por mes devuelve el dia con horario', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/disponibilidad?servicio={$servicio->id}&mes={$fecha->format('Y-m')}");

    $respuesta->assertOk();
    expect($respuesta->json('dias'))->toContain($fecha->toDateString());
});

test('GET disponibilidad por fecha coincide con el motor de disponibilidad', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/disponibilidad?servicio={$servicio->id}&profesional={$profesional->id}&fecha={$fecha->toDateString()}");

    $respuesta->assertOk();
    $respuesta->assertJsonStructure(['data' => [['profesionalId', 'inicio', 'fin', 'fechaVisible', 'horaVisible', 'horaFinVisible']]]);
    $horas = collect($respuesta->json('data'))->pluck('horaVisible');
    expect($horas->all())->toBe(['10:00 a. m.', '11:00 a. m.']);
    expect($respuesta->json('data.0.horaFinVisible'))->toBe('11:00 a. m.');
    expect($respuesta->json('data.0.fechaVisible'))->toBe(Fecha::formatear($fecha, 'America/Bogota'));
});

test('GET disponibilidad rechaza mandar mes y fecha juntos', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/disponibilidad?servicio={$servicio->id}&mes={$fecha->format('Y-m')}&fecha={$fecha->toDateString()}");

    $respuesta->assertStatus(422);
});

test('POST reservas crea la cita con cualquier profesional disponible', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas", [
        'servicio_id' => $servicio->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc()->toIso8601String(),
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'correo' => 'ana@example.com',
        'autorizacion_datos' => true,
    ]);

    $respuesta->assertCreated();
    $respuesta->assertJsonStructure(['data' => ['id', 'servicio', 'profesional', 'cliente', 'inicio', 'fin', 'fechaVisible', 'horaVisible', 'precio', 'estado', 'direccion', 'whatsapp', 'nombreNegocio']]);
    expect(Cita::query()->where('profesional_id', $profesional->id)->count())->toBe(1);
    expect(Cliente::query()->where('celular', '3001234567')->count())->toBe(1);
});

test('POST reservas exige autorizar el tratamiento de datos', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas", [
        'servicio_id' => $servicio->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc()->toIso8601String(),
        'nombre' => 'Ana',
        'celular' => '3001234567',
    ]);

    $respuesta->assertStatus(422);
    $respuesta->assertJsonValidationErrors(['autorizacion_datos']);
});

test('POST reservas rechaza el envio si el campo trampa viene lleno', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas", [
        'servicio_id' => $servicio->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc()->toIso8601String(),
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'autorizacion_datos' => true,
        'sitio_web' => 'https://spam.example.com',
    ]);

    $respuesta->assertStatus(422);
});

test('POST reservas con una hora ya tomada devuelve 409 con horas cercanas', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => Cliente::factory()->create(['negocio_id' => $negocio->id])->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc(),
        'fin' => $fecha->copy()->setTime(11, 0)->utc(),
    ]);

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas", [
        'servicio_id' => $servicio->id,
        'profesional_id' => $profesional->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc()->toIso8601String(),
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'autorizacion_datos' => true,
    ]);

    $respuesta->assertStatus(409);
    expect($respuesta->json('horasCercanas'))->not->toBeEmpty();
});

test('un negocio suspendido no expone horas ni acepta reservas', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();
    $negocio->update(['estado_suscripcion' => EstadoSuscripcion::Suspendido]);

    $respuestaServicios = $this->getJson("/api/publico/{$negocio->slug}/servicios");
    $respuestaDisponibilidad = $this->getJson("/api/publico/{$negocio->slug}/disponibilidad?servicio={$servicio->id}&fecha={$fecha->toDateString()}");
    $respuestaReserva = $this->postJson("/api/publico/{$negocio->slug}/reservas", [
        'servicio_id' => $servicio->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc()->toIso8601String(),
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'autorizacion_datos' => true,
    ]);

    $respuestaServicios->assertStatus(404);
    $respuestaDisponibilidad->assertStatus(404);
    $respuestaReserva->assertStatus(404);
});

test('un slug que no existe responde 404', function (): void {
    $respuesta = $this->getJson('/api/publico/negocio-que-no-existe/servicios');

    $respuesta->assertStatus(404);
});

test('el endpoint de reservas tiene un limite de intentos estricto', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicio, 'fecha' => $fecha] = negocioPublicoListo();

    for ($intento = 1; $intento <= 5; $intento++) {
        $this->postJson("/api/publico/{$negocio->slug}/reservas", [
            'servicio_id' => $servicio->id,
            'inicio' => $fecha->copy()->setTime(10, 0)->utc()->toIso8601String(),
            'nombre' => 'Ana',
            'celular' => '3001234567',
            'autorizacion_datos' => true,
        ]);
    }

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas", [
        'servicio_id' => $servicio->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc()->toIso8601String(),
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'autorizacion_datos' => true,
    ]);

    $respuesta->assertStatus(429);
});
