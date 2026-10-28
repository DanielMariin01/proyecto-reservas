<?php

declare(strict_types=1);

use App\Actions\ReprogramarCita;
use App\DatosTransferencia\DatosReprogramacion;
use App\Enums\DiaSemana;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Events\CitaReprogramada;
use App\Exceptions\FueraDePoliticaDeCancelacionException;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\MaximoDeReprogramacionesException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioDisponibilidad;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;

/**
 * Negocio con horario todos los martes 10:00-12:00 (hora Bogota), una cita
 * el martes a las 10:00, y "ahora" bastante antes para que la politica de
 * reprogramacion nunca interfiera salvo que la prueba la busque a
 * proposito.
 *
 * @return array{negocio: Negocio, profesional: Profesional, servicio: Servicio, cita: Cita, fecha: CarbonImmutable}
 */
function negocioConCitaReprogramable(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-01 00:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create([
        'zona_horaria' => 'America/Bogota',
        'horas_minimas_reprogramacion' => 2,
        'maximo_reprogramaciones' => 2,
    ]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
    ]);

    $fecha = CarbonImmutable::now('America/Bogota')->next(CarbonImmutable::TUESDAY);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc(),
        'fin' => $fecha->copy()->setTime(11, 0)->utc(),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'cita' => $cita, 'fecha' => $fecha];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('reprogramar deja la cita en confirmada y mueve la hora', function (): void {
    ['cita' => $cita, 'fecha' => $fecha] = negocioConCitaReprogramable();

    $nuevoInicio = $fecha->copy()->setTime(11, 0)->utc();

    $cita = app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion($cita, $nuevoInicio));

    expect($cita->estado)->toBe(EstadoCitaValor::Confirmada);
    expect($cita->inicio->equalTo($nuevoInicio))->toBeTrue();
    expect($cita->veces_reprogramada)->toBe(1);
});

test('la franja vieja queda libre y la nueva ocupada tras reprogramar', function (): void {
    ['profesional' => $profesional, 'servicio' => $servicio, 'cita' => $cita, 'fecha' => $fecha] = negocioConCitaReprogramable();

    app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion($cita, $fecha->copy()->setTime(11, 0)->utc()));

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fecha);
    $horasTexto = $horas->map(fn ($h) => $h->inicio->setTimezone('America/Bogota')->format('H:i'))->values()->all();

    expect($horasTexto)->toContain('10:00');
    expect($horasTexto)->not->toContain('11:00');
});

test('respeta el maximo de reprogramaciones', function (): void {
    ['negocio' => $negocio, 'cita' => $cita, 'fecha' => $fecha] = negocioConCitaReprogramable();

    $cita->update(['veces_reprogramada' => $negocio->maximo_reprogramaciones]);

    $ejecutar = fn () => app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion($cita, $fecha->copy()->setTime(11, 0)->utc()));

    expect($ejecutar)->toThrow(MaximoDeReprogramacionesException::class);
});

test('el cliente no puede reprogramar dentro de la ventana de politica', function (): void {
    ['cita' => $cita, 'fecha' => $fecha] = negocioConCitaReprogramable();

    // Mueve "ahora" a menos de 2 horas antes de la cita (10:00).
    CarbonImmutable::setTestNow($fecha->copy()->setTime(9, 0));

    $ejecutar = fn () => app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion($cita, $fecha->copy()->setTime(11, 0)->utc()));

    expect($ejecutar)->toThrow(FueraDePoliticaDeCancelacionException::class);
});

test('un dueno con permiso puede reprogramar dentro de la ventana de politica', function (): void {
    ['negocio' => $negocio, 'cita' => $cita, 'fecha' => $fecha] = negocioConCitaReprogramable();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    CarbonImmutable::setTestNow($fecha->copy()->setTime(9, 0));

    $cita = app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion($cita, $fecha->copy()->setTime(11, 0)->utc(), reprogramadaPor: $dueno));

    expect($cita->estado)->toBe(EstadoCitaValor::Confirmada);
});

test('rechaza una hora que no es una franja valida al reprogramar', function (): void {
    ['cita' => $cita, 'fecha' => $fecha] = negocioConCitaReprogramable();

    $ejecutar = fn () => app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion($cita, $fecha->copy()->setTime(14, 0)->utc()));

    expect($ejecutar)->toThrow(HoraInvalidaException::class);
});

test('dispara el evento CitaReprogramada', function (): void {
    Event::fake();
    ['cita' => $cita, 'fecha' => $fecha] = negocioConCitaReprogramable();

    app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion($cita, $fecha->copy()->setTime(11, 0)->utc()));

    Event::assertDispatched(CitaReprogramada::class);
});
