<?php

declare(strict_types=1);

use App\Actions\CambiarEstadoCita;
use App\Enums\EstadoCitaValor;
use App\Events\CitaCambioDeEstado;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Support\Facades\Event;

function citaConfirmada(): Cita
{
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    return Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
    ]);
}

test('una cita confirmada puede pasar a en atencion y luego a completada', function (): void {
    $cita = citaConfirmada();

    $cita = app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::EnAtencion);
    expect($cita->estado)->toBe(EstadoCitaValor::EnAtencion);

    $cita = app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::Completada);
    expect($cita->estado)->toBe(EstadoCitaValor::Completada);
});

test('una cita confirmada puede marcarse como no asistio', function (): void {
    $cita = citaConfirmada();

    $cita = app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::NoAsistio);

    expect($cita->estado)->toBe(EstadoCitaValor::NoAsistio);
});

test('no se puede pasar de cancelada a completada', function (): void {
    $cita = citaConfirmada();
    $cita->update(['estado' => EstadoCitaValor::Cancelada]);

    $ejecutar = fn () => app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::Completada);

    expect($ejecutar)->toThrow(TransicionDeEstadoInvalidaException::class);
});

test('dispara el evento CitaCambioDeEstado', function (): void {
    Event::fake();
    $cita = citaConfirmada();

    app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::EnAtencion);

    Event::assertDispatched(CitaCambioDeEstado::class);
});
