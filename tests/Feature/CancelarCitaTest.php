<?php

declare(strict_types=1);

use App\Actions\CancelarCita;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Events\CitaCancelada;
use App\Exceptions\FueraDePoliticaDeCancelacionException;
use App\Exceptions\TransicionDeEstadoInvalidaException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;

/**
 * @return array{negocio: Negocio, cita: Cita}
 */
function negocioConCitaEnUnaHora(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-01 10:00:00', 'UTC'));

    $negocio = Negocio::factory()->create(['horas_minimas_cancelacion' => 2]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-01 11:00:00', 'UTC'),
        'fin' => CarbonImmutable::parse('2026-12-01 11:45:00', 'UTC'),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    return ['negocio' => $negocio, 'cita' => $cita];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('cancelar libera la franja de inmediato', function (): void {
    ['negocio' => $negocio, 'cita' => $cita] = negocioConCitaEnUnaHora();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    app(CancelarCita::class)->ejecutar($cita, 'Ya no puedo ir', canceladoPor: $dueno);

    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Cancelada);
    expect($cita->motivo_cancelacion)->toBe('Ya no puedo ir');
});

test('dispara el evento CitaCancelada', function (): void {
    Event::fake();
    ['negocio' => $negocio, 'cita' => $cita] = negocioConCitaEnUnaHora();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    app(CancelarCita::class)->ejecutar($cita, 'Motivo', canceladoPor: $dueno);

    Event::assertDispatched(CitaCancelada::class);
});

test('el cliente no puede cancelar dentro de la ventana de politica', function (): void {
    // "ahora" = 10:00, cita a las 11:00, politica de 2 horas: faltan menos de 2h.
    ['cita' => $cita] = negocioConCitaEnUnaHora();

    $ejecutar = fn () => app(CancelarCita::class)->ejecutar($cita, 'Motivo', canceladoPor: null);

    expect($ejecutar)->toThrow(FueraDePoliticaDeCancelacionException::class);
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Confirmada);
});

test('un dueno con permiso puede cancelar fuera de la ventana de politica', function (): void {
    ['negocio' => $negocio, 'cita' => $cita] = negocioConCitaEnUnaHora();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    app(CancelarCita::class)->ejecutar($cita, 'Motivo', canceladoPor: $dueno);

    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Cancelada);
});

test('un profesional sin permiso de gestionar todas las citas respeta la politica', function (): void {
    ['negocio' => $negocio, 'cita' => $cita] = negocioConCitaEnUnaHora();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $ejecutar = fn () => app(CancelarCita::class)->ejecutar($cita, 'Motivo', canceladoPor: $profesionalUsuario);

    expect($ejecutar)->toThrow(FueraDePoliticaDeCancelacionException::class);
});

test('no se puede cancelar una cita ya completada', function (): void {
    ['cita' => $cita] = negocioConCitaEnUnaHora();
    $cita->update(['estado' => EstadoCitaValor::Completada]);

    $ejecutar = fn () => app(CancelarCita::class)->ejecutar($cita, 'Motivo');

    expect($ejecutar)->toThrow(TransicionDeEstadoInvalidaException::class);
});
