<?php

declare(strict_types=1);

use App\Enums\DiaSemana;
use App\Enums\EstadoCitaValor;
use App\Enums\TipoBloqueo;
use App\Models\Bloqueo;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioDisponibilidad;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Crea un negocio en America/Bogota y un lunes futuro (para que el aviso
 * mínimo y la ventana máxima nunca interfieran salvo que la prueba lo
 * busque a propósito).
 *
 * @return array{negocio: Negocio, fecha: CarbonImmutable}
 */
function negocioYLunes(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-01 00:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $fecha = CarbonImmutable::now('America/Bogota')->next(CarbonImmutable::MONDAY);

    return ['negocio' => $negocio, 'fecha' => $fecha];
}

function horasVisibles(Collection $horas): array
{
    return $horas
        ->map(fn ($hora) => $hora->inicio->setTimezone('America/Bogota')->format('H:i'))
        ->values()
        ->all();
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('el ejemplo de Japo: intervalo igual a la duracion da franjas cada 45 minutos', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 45,
        'margen_antes_minutos' => 0,
        'margen_despues_minutos' => 0,
        'intervalo_franjas_minutos' => null,
    ]);

    foreach ([['10:00:00', '12:00:00'], ['14:00:00', '18:00:00']] as [$inicio, $fin]) {
        HorarioTrabajo::factory()->create([
            'negocio_id' => $negocio->id,
            'profesional_id' => $profesional->id,
            'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
        ]);
    }

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fecha);

    expect(horasVisibles($horas))->toBe(['10:00', '10:45', '14:00', '14:45', '15:30', '16:15', '17:00']);
});

test('el ejemplo de Japo con intervalo flexible de 15 minutos si muestra las 11:00', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 45,
        'margen_antes_minutos' => 0,
        'margen_despues_minutos' => 0,
        'intervalo_franjas_minutos' => 15,
    ]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fecha);

    expect(horasVisibles($horas))->toContain('11:00');
});

test('un bloqueo guardado en UTC resta la porcion correcta del bloque local', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 30,
        'intervalo_franjas_minutos' => 30,
    ]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '09:00:00',
        'hora_fin' => '12:00:00',
    ]);

    // 10:00-11:00 hora de Bogota (UTC-5) = 15:00-16:00 UTC.
    $fechaTexto = $fecha->toDateString();
    Bloqueo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'tipo' => TipoBloqueo::Bloqueo,
        'inicio' => CarbonImmutable::parse("{$fechaTexto} 15:00:00", 'UTC'),
        'fin' => CarbonImmutable::parse("{$fechaTexto} 16:00:00", 'UTC'),
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fecha);

    expect(horasVisibles($horas))->toBe(['09:00', '09:30', '11:00', '11:30']);
});

test('una apertura especial da franjas un domingo sin horario semanal', function (): void {
    ['negocio' => $negocio, 'fecha' => $lunes] = negocioYLunes();
    $domingo = $lunes->subDay();

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
    ]);

    Bloqueo::factory()->apertura()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'inicio' => $domingo->copy()->setTime(10, 0)->utc(),
        'fin' => $domingo->copy()->setTime(14, 0)->utc(),
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $domingo);

    expect(horasVisibles($horas))->toBe(['10:00', '11:00', '12:00', '13:00']);
});

test('un margen que no cabe antes del cierre del bloque descarta la franja', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 45,
        'margen_antes_minutos' => 0,
        'margen_despues_minutos' => 10,
        'intervalo_franjas_minutos' => 45,
    ]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '10:50:00',
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fecha);

    expect($horas)->toBeEmpty();
});

test('el aviso minimo descarta franjas demasiado proximas a ahora', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();
    $negocio->update(['aviso_minimo_minutos' => 60]);

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
    ]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    CarbonImmutable::setTestNow($fecha->copy()->setTime(10, 0));

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fecha);

    expect(horasVisibles($horas))->toBe(['11:00']);
});

test('la ventana maxima descarta un dia mas alla del limite configurado', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();
    $negocio->update(['ventana_maxima_dias' => 5]);

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 60]);

    $fechaLejana = $fecha->addDays(10);
    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fechaLejana->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fechaLejana);

    expect($horas)->toBeEmpty();
});

test('un bloqueo que cruza de diciembre a enero resta en ambos dias', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-30 00:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
    ]);

    $nochevieja = CarbonImmutable::parse('2026-12-31', 'America/Bogota');
    $anioNuevo = $nochevieja->addDay();

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($nochevieja->dayOfWeek),
        'hora_inicio' => '22:00:00',
        'hora_fin' => '23:59:59',
    ]);
    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($anioNuevo->dayOfWeek),
        'hora_inicio' => '00:00:00',
        'hora_fin' => '02:00:00',
    ]);

    Bloqueo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'tipo' => TipoBloqueo::Bloqueo,
        'inicio' => $nochevieja->copy()->setTime(23, 0)->utc(),
        'fin' => $anioNuevo->copy()->setTime(1, 0)->utc(),
    ]);

    $horasNochevieja = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $nochevieja);
    $horasAnioNuevo = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $anioNuevo);

    expect(horasVisibles($horasNochevieja))->toBe(['22:00']);
    expect(horasVisibles($horasAnioNuevo))->toBe(['01:00']);
});

test('cualquier profesional une las franjas de todos sin duplicados', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();

    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
    ]);

    $profesionalA = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $profesionalB = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesionalA->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '11:00:00',
    ]);
    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesionalB->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '11:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasParaCualquierProfesional(
        collect([$profesionalA, $profesionalB]),
        $servicio,
        $fecha,
    );

    expect(horasVisibles($horas))->toBe(['10:00', '11:00']);
});

test('un servicio sin margen propio usa el margen del negocio', function (): void {
    $negocio = Negocio::factory()->create(['margen_antes_minutos' => 5, 'margen_despues_minutos' => 10]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'margen_antes_minutos' => null,
        'margen_despues_minutos' => null,
    ]);

    expect($servicio->margenAntesMinutos())->toBe(5);
    expect($servicio->margenDespuesMinutos())->toBe(10);
});

test('un servicio con margen propio sobrescribe el del negocio', function (): void {
    $negocio = Negocio::factory()->create(['margen_antes_minutos' => 5, 'margen_despues_minutos' => 10]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'margen_antes_minutos' => 0,
        'margen_despues_minutos' => 2,
    ]);

    expect($servicio->margenAntesMinutos())->toBe(0);
    expect($servicio->margenDespuesMinutos())->toBe(2);
});

test('el horario de un negocio no afecta la disponibilidad de otro', function (): void {
    ['negocio' => $negocioA, 'fecha' => $fecha] = negocioYLunes();
    $negocioB = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);

    $profesionalA = Profesional::factory()->create(['negocio_id' => $negocioA->id]);
    $profesionalB = Profesional::factory()->create(['negocio_id' => $negocioB->id]);
    $servicioA = Servicio::factory()->create(['negocio_id' => $negocioA->id, 'duracion_minutos' => 60, 'intervalo_franjas_minutos' => 60]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocioB->id,
        'profesional_id' => $profesionalB->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '08:00:00',
        'hora_fin' => '09:00:00',
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesionalA, $servicioA, $fecha);

    expect($horas)->toBeEmpty();
});

test('la cache de bloques libres se invalida sola al agregar un bloqueo nuevo', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
    ]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $servicioDisponibilidad = app(ServicioDisponibilidad::class);

    $primeraLectura = $servicioDisponibilidad->obtenerFranjasDisponibles($profesional, $servicio, $fecha);
    expect(horasVisibles($primeraLectura))->toBe(['10:00', '11:00']);

    Bloqueo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'tipo' => TipoBloqueo::Bloqueo,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc(),
        'fin' => $fecha->copy()->setTime(11, 0)->utc(),
    ]);

    $segundaLectura = $servicioDisponibilidad->obtenerFranjasDisponibles($profesional, $servicio, $fecha);

    expect(horasVisibles($segundaLectura))->toBe(['11:00']);
});

test('una cita activa resta su franja ampliada con los margenes del servicio', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicioOcupante = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 30,
        'margen_antes_minutos' => 10,
        'margen_despues_minutos' => 0,
    ]);
    $servicioConsultado = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 30,
        'intervalo_franjas_minutos' => 30,
    ]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    // Cita visible 11:00-11:30, con 10 min de margen antes: ocupa 10:50-11:30
    // de verdad, lo suficiente para tumbar la franja de las 10:30.
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicioOcupante->id,
        'cliente_id' => Cliente::factory()->create(['negocio_id' => $negocio->id])->id,
        'inicio' => $fecha->copy()->setTime(11, 0)->utc(),
        'fin' => $fecha->copy()->setTime(11, 30)->utc(),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicioConsultado, $fecha);

    expect(horasVisibles($horas))->toBe(['10:00', '11:30']);
});

test('una cita cancelada no resta nada de la disponibilidad', function (): void {
    ['negocio' => $negocio, 'fecha' => $fecha] = negocioYLunes();

    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
    ]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    Cita::factory()->cancelada()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => Cliente::factory()->create(['negocio_id' => $negocio->id])->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc(),
        'fin' => $fecha->copy()->setTime(11, 0)->utc(),
    ]);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fecha);

    expect(horasVisibles($horas))->toBe(['10:00', '11:00']);
});
