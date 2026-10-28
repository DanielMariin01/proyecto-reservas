<?php

declare(strict_types=1);

use App\Enums\DiaSemana;
use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioDisponibilidad;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Negocio con horario todos los martes 10:00-12:00 (hora Bogota) y una
 * cita el martes siguiente a las 10:00, con "ahora" bastante antes para
 * que la politica de cancelacion/reprogramacion no interfiera salvo que
 * la prueba la busque a proposito.
 *
 * @return array{negocio: Negocio, profesional: Profesional, servicio: Servicio, cita: Cita, fecha: CarbonImmutable, token: string}
 */
function negocioConCitaConToken(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-01 00:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create([
        'zona_horaria' => 'America/Bogota',
        'horas_minimas_cancelacion' => 2,
        'horas_minimas_reprogramacion' => 2,
        'maximo_reprogramaciones' => 2,
    ]);
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

    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);
    $token = Str::random(40);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc(),
        'fin' => $fecha->copy()->setTime(11, 0)->utc(),
        'estado' => EstadoCitaValor::Confirmada,
        'token_cancelacion' => $token,
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'cita' => $cita, 'fecha' => $fecha, 'token' => $token];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('ver una cita por su token no requiere sesion', function (): void {
    ['negocio' => $negocio, 'token' => $token] = negocioConCitaConToken();

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/reservas/{$token}");

    $respuesta->assertOk();
    $respuesta->assertJsonPath('data.puedeCancelar', true);
    $respuesta->assertJsonPath('data.puedeReprogramar', true);
    $respuesta->assertJsonPath('data.enlaceActivo', true);
});

test('un token inexistente responde 404', function (): void {
    $negocio = Negocio::factory()->create();

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/reservas/".Str::random(40));

    $respuesta->assertNotFound();
});

test('el token de un negocio no sirve bajo el slug de otro', function (): void {
    ['token' => $token] = negocioConCitaConToken();
    $otroNegocio = Negocio::factory()->create();

    $respuesta = $this->getJson("/api/publico/{$otroNegocio->slug}/reservas/{$token}");

    $respuesta->assertNotFound();
});

test('cancelar por token libera la franja y el token deja de servir para cambios', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'cita' => $cita, 'fecha' => $fecha, 'token' => $token] =
        negocioConCitaConToken();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas/{$token}", [
        'accion' => 'cancelar',
        'motivo' => 'Se me complicó',
    ]);

    $respuesta->assertOk();
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Cancelada);

    $horas = app(ServicioDisponibilidad::class)->obtenerFranjasDisponibles($profesional, $servicio, $fecha);
    $horasTexto = $horas->map(fn ($h) => $h->inicio->setTimezone('America/Bogota')->format('H:i'))->values()->all();
    expect($horasTexto)->toContain('10:00');

    $siguiente = $this->getJson("/api/publico/{$negocio->slug}/reservas/{$token}");
    $siguiente->assertJsonPath('data.enlaceActivo', false);
    $siguiente->assertJsonPath('data.puedeCancelar', false);
});

test('reprogramar por token mueve la cita a la nueva hora', function (): void {
    ['negocio' => $negocio, 'cita' => $cita, 'fecha' => $fecha, 'token' => $token] = negocioConCitaConToken();

    $nuevoInicio = $fecha->copy()->setTime(11, 0)->utc()->toIso8601String();

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas/{$token}", [
        'accion' => 'reprogramar',
        'inicio' => $nuevoInicio,
    ]);

    $respuesta->assertOk();
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Confirmada);
    expect($cita->inicio->equalTo(CarbonImmutable::parse($nuevoInicio)))->toBeTrue();
});

test('cancelar fuera de la ventana de politica responde con la opcion de whatsapp', function (): void {
    ['negocio' => $negocio, 'cita' => $cita, 'token' => $token] = negocioConCitaConToken();
    CarbonImmutable::setTestNow($cita->inicio->subMinutes(30));

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/reservas/{$token}", [
        'accion' => 'cancelar',
    ]);

    $respuesta->assertStatus(422);
    $respuesta->assertJsonPath('mostrarWhatsapp', true);
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Confirmada);
});
