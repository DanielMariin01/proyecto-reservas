<?php

declare(strict_types=1);

use App\Enums\EstadoCitaValor;
use App\Enums\OrigenCita;
use App\Exceptions\HoraYaTomadaException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioReservas;
use Carbon\CarbonImmutable;

test('reserva una cita confirmada cuando el negocio no exige confirmacion manual', function (): void {
    $negocio = Negocio::factory()->create(['confirmacion_manual' => false]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 30, 'precio' => 20000]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $inicio = CarbonImmutable::parse('2026-12-01 15:00:00', 'UTC');

    $cita = app(ServicioReservas::class)->reservar($profesional, $servicio, $cliente, $inicio, OrigenCita::Web);

    expect($cita->estado)->toBe(EstadoCitaValor::Confirmada);
    expect($cita->precio)->toBe(20000);
    expect($cita->token_cancelacion)->not->toBeNull();
});

test('reserva una cita pendiente cuando el negocio exige confirmacion manual', function (): void {
    $negocio = Negocio::factory()->create(['confirmacion_manual' => true]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 30]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = app(ServicioReservas::class)->reservar(
        $profesional,
        $servicio,
        $cliente,
        CarbonImmutable::parse('2026-12-01 15:00:00', 'UTC'),
        OrigenCita::Web,
    );

    expect($cita->estado)->toBe(EstadoCitaValor::Pendiente);
});

test('rechaza una hora que choca con una cita activa del mismo profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 60]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-01 15:00:00', 'UTC'),
        'fin' => CarbonImmutable::parse('2026-12-01 16:00:00', 'UTC'),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    $ejecutar = fn () => app(ServicioReservas::class)->reservar(
        $profesional,
        $servicio,
        $cliente,
        CarbonImmutable::parse('2026-12-01 15:30:00', 'UTC'),
        OrigenCita::Panel,
    );

    expect($ejecutar)->toThrow(HoraYaTomadaException::class);
    expect(Cita::query()->where('profesional_id', $profesional->id)->count())->toBe(1);
});

test('una cita cancelada no choca con una hora nueva en el mismo horario', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 60]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->cancelada()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-01 15:00:00', 'UTC'),
        'fin' => CarbonImmutable::parse('2026-12-01 16:00:00', 'UTC'),
    ]);

    $cita = app(ServicioReservas::class)->reservar(
        $profesional,
        $servicio,
        $cliente,
        CarbonImmutable::parse('2026-12-01 15:00:00', 'UTC'),
        OrigenCita::Panel,
    );

    expect($cita->estado)->toBe(EstadoCitaValor::Confirmada);
});

test('forzar guarda la cita aunque choque con otra', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 60]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-01 15:00:00', 'UTC'),
        'fin' => CarbonImmutable::parse('2026-12-01 16:00:00', 'UTC'),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    app(ServicioReservas::class)->reservar(
        $profesional,
        $servicio,
        $cliente,
        CarbonImmutable::parse('2026-12-01 15:30:00', 'UTC'),
        OrigenCita::Panel,
        forzar: true,
    );

    expect(Cita::query()->where('profesional_id', $profesional->id)->count())->toBe(2);
});

test('moverA excluye la propia cita de la verificacion de choque', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 60]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-01 15:00:00', 'UTC'),
        'fin' => CarbonImmutable::parse('2026-12-01 16:00:00', 'UTC'),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    $movida = app(ServicioReservas::class)->moverA($cita, CarbonImmutable::parse('2026-12-02 10:00:00', 'UTC'), 60);

    expect($movida->inicio->toIso8601String())->toBe(CarbonImmutable::parse('2026-12-02 10:00:00', 'UTC')->toIso8601String());
});
