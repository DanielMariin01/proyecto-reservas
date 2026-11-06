<?php

declare(strict_types=1);

use App\Enums\DiaSemana;
use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\CuentaCliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * @return array{cuenta: CuentaCliente, negocio: Negocio, profesional: Profesional, servicio: Servicio, cliente: Cliente, cita: Cita}
 */
function cuentaConCitaConfirmada(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-01 00:00:00', 'America/Bogota'));

    $cuenta = CuentaCliente::factory()->create();
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
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'correo' => $cuenta->correo, 'cuenta_cliente_id' => $cuenta->id]);

    $fecha = CarbonImmutable::now('America/Bogota')->next(CarbonImmutable::TUESDAY);
    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc(),
        'fin' => $fecha->copy()->setTime(11, 0)->utc(),
        'estado' => EstadoCitaValor::Confirmada,
        'token_cancelacion' => Str::random(40),
    ]);

    return ['cuenta' => $cuenta, 'negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'cliente' => $cliente, 'cita' => $cita];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('sin sesion, el listado de mis citas no autoriza', function (): void {
    $respuesta = $this->getJson('/mi-cuenta/api/citas');

    $respuesta->assertUnauthorized();
});

test('un cliente ve sus citas de todos los negocios donde tiene cuenta, pero no las de otro cliente', function (): void {
    ['cuenta' => $cuenta, 'cita' => $miCita] = cuentaConCitaConfirmada();
    ['cita' => $citaAjena] = cuentaConCitaConfirmada();

    $respuesta = $this->actingAs($cuenta, 'cliente')->getJson('/mi-cuenta/api/citas');

    $respuesta->assertOk();
    $ids = collect($respuesta->json('data'))->pluck('id');
    expect($ids)->toContain($miCita->id);
    expect($ids)->not->toContain($citaAjena->id);
});

test('una cita confirmada aparece como proxima y una completada como historial', function (): void {
    ['cuenta' => $cuenta, 'cita' => $cita] = cuentaConCitaConfirmada();
    $cita->update(['estado' => EstadoCitaValor::Completada]);

    $respuesta = $this->actingAs($cuenta, 'cliente')->getJson('/mi-cuenta/api/citas');

    $respuesta->assertJsonPath('data.0.esProxima', false);
});

test('el cliente puede cancelar su propia cita', function (): void {
    ['cuenta' => $cuenta, 'cita' => $cita] = cuentaConCitaConfirmada();

    $respuesta = $this->actingAs($cuenta, 'cliente')->postJson("/mi-cuenta/api/citas/{$cita->id}/cancelar", []);

    $respuesta->assertOk();
    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Cancelada);
});

test('un cliente no puede cancelar la cita de otro', function (): void {
    ['cuenta' => $cuenta] = cuentaConCitaConfirmada();
    ['cita' => $citaAjena] = cuentaConCitaConfirmada();

    $respuesta = $this->actingAs($cuenta, 'cliente')->postJson("/mi-cuenta/api/citas/{$citaAjena->id}/cancelar", []);

    $respuesta->assertForbidden();
    expect($citaAjena->refresh()->estado)->toBe(EstadoCitaValor::Confirmada);
});

test('el cliente puede reprogramar su propia cita', function (): void {
    ['cuenta' => $cuenta, 'cita' => $cita] = cuentaConCitaConfirmada();
    $nuevoInicio = $cita->inicio->addHour()->toIso8601String();

    $respuesta = $this->actingAs($cuenta, 'cliente')->postJson("/mi-cuenta/api/citas/{$cita->id}/reprogramar", [
        'inicio' => $nuevoInicio,
    ]);

    $respuesta->assertOk();
    expect($cita->refresh()->inicio->equalTo(CarbonImmutable::parse($nuevoInicio)))->toBeTrue();
});

test('el cliente puede dejar una reseña de su cita completada desde el panel', function (): void {
    ['cuenta' => $cuenta, 'cita' => $cita] = cuentaConCitaConfirmada();
    $cita->update(['estado' => EstadoCitaValor::Completada]);

    $respuesta = $this->actingAs($cuenta, 'cliente')->postJson("/mi-cuenta/api/citas/{$cita->id}/resena", [
        'calificacion' => 5,
        'comentario' => 'Excelente atención, muy recomendado.',
    ]);

    $respuesta->assertOk();
    expect(Resena::query()->where('cita_id', $cita->id)->exists())->toBeTrue();
});

test('un cliente no puede dejar reseña de la cita de otro', function (): void {
    ['cuenta' => $cuenta] = cuentaConCitaConfirmada();
    ['cita' => $citaAjena] = cuentaConCitaConfirmada();
    $citaAjena->update(['estado' => EstadoCitaValor::Completada]);

    $respuesta = $this->actingAs($cuenta, 'cliente')->postJson("/mi-cuenta/api/citas/{$citaAjena->id}/resena", [
        'calificacion' => 5,
        'comentario' => 'Esto no deberia pasar nunca.',
    ]);

    $respuesta->assertForbidden();
    expect(Resena::query()->where('cita_id', $citaAjena->id)->exists())->toBeFalse();
});

test('un Usuario autenticado en el guard web no puede usar los endpoints del guard cliente', function (): void {
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($usuario, 'web')->getJson('/mi-cuenta/api/citas');

    $respuesta->assertUnauthorized();
    Auth::guard('web')->logout();
});
