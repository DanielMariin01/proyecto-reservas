<?php

declare(strict_types=1);

use App\Actions\CrearReserva;
use App\DatosTransferencia\DatosReserva;
use App\Enums\DiaSemana;
use App\Enums\EstadoCitaValor;
use App\Enums\OrigenCita;
use App\Events\CitaCreada;
use App\Exceptions\HoraInvalidaException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;

/**
 * @return array{negocio: Negocio, profesional: Profesional, servicio: Servicio, fecha: CarbonImmutable}
 */
function negocioConHorarioMartes(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-01 00:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
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

test('crea un cliente nuevo cuando el celular no existe en el negocio', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioConHorarioMartes();

    $cita = app(CrearReserva::class)->ejecutar(new DatosReserva(
        negocio: $negocio,
        servicio: $servicio,
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: 'ana@example.com',
        inicio: $fecha->copy()->setTime(10, 0)->utc(),
        origen: OrigenCita::Web,
        profesional: $profesional,
    ));

    expect(Cliente::query()->where('negocio_id', $negocio->id)->where('celular', '3001234567')->count())->toBe(1);
    expect($cita->cliente_id)->toBe(Cliente::query()->where('celular', '3001234567')->first()->id);
});

test('reutiliza el cliente existente con el mismo celular en vez de duplicarlo', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioConHorarioMartes();

    $clienteExistente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'celular' => '3001234567']);

    $cita = app(CrearReserva::class)->ejecutar(new DatosReserva(
        negocio: $negocio,
        servicio: $servicio,
        nombreCliente: 'Otro nombre',
        celularCliente: '3001234567',
        correoCliente: null,
        inicio: $fecha->copy()->setTime(10, 0)->utc(),
        origen: OrigenCita::Web,
        profesional: $profesional,
    ));

    expect(Cliente::query()->where('negocio_id', $negocio->id)->where('celular', '3001234567')->count())->toBe(1);
    expect($cita->cliente_id)->toBe($clienteExistente->id);
});

test('con cualquier profesional asigna al que tenga menos citas ese dia', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesionalA, 'servicio' => $servicio, 'fecha' => $fecha] = negocioConHorarioMartes();

    $profesionalB = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio->profesionales()->attach($profesionalB->id);
    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesionalB->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    // profesionalA ya tiene una cita ese dia; profesionalB no tiene ninguna.
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesionalA->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => Cliente::factory()->create(['negocio_id' => $negocio->id])->id,
        'inicio' => $fecha->copy()->setTime(8, 0)->utc(),
        'fin' => $fecha->copy()->setTime(9, 0)->utc(),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    $cita = app(CrearReserva::class)->ejecutar(new DatosReserva(
        negocio: $negocio,
        servicio: $servicio,
        nombreCliente: 'Ana',
        celularCliente: '3009999999',
        correoCliente: null,
        inicio: $fecha->copy()->setTime(10, 0)->utc(),
        origen: OrigenCita::Web,
        profesional: null,
    ));

    expect($cita->profesional_id)->toBe($profesionalB->id);
});

test('dispara el evento CitaCreada al reservar', function (): void {
    Event::fake();

    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioConHorarioMartes();

    app(CrearReserva::class)->ejecutar(new DatosReserva(
        negocio: $negocio,
        servicio: $servicio,
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        inicio: $fecha->copy()->setTime(10, 0)->utc(),
        origen: OrigenCita::Web,
        profesional: $profesional,
    ));

    Event::assertDispatched(CitaCreada::class);
});

test('rechaza una hora que no es una franja valida', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioConHorarioMartes();

    $ejecutar = fn () => app(CrearReserva::class)->ejecutar(new DatosReserva(
        negocio: $negocio,
        servicio: $servicio,
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        inicio: $fecha->copy()->setTime(13, 0)->utc(), // fuera del horario 10:00-12:00
        origen: OrigenCita::Web,
        profesional: $profesional,
    ));

    expect($ejecutar)->toThrow(HoraInvalidaException::class);
    expect(Cita::query()->count())->toBe(0);
});

test('forzar permite reservar una hora fuera de las franjas validas', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioConHorarioMartes();

    $cita = app(CrearReserva::class)->ejecutar(new DatosReserva(
        negocio: $negocio,
        servicio: $servicio,
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        inicio: $fecha->copy()->setTime(13, 0)->utc(),
        origen: OrigenCita::Panel,
        profesional: $profesional,
        forzar: true,
    ));

    expect(Cita::query()->whereKey($cita->id)->exists())->toBeTrue();
});
