<?php

declare(strict_types=1);

use App\Enums\EstadoCitaValor;
use App\Enums\Permiso;
use App\Enums\RolNegocio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioAccionesCita;
use Spatie\Permission\PermissionRegistrar;

function crearCitaDePrueba(Negocio $negocio, Profesional $profesional, EstadoCitaValor $estado): Cita
{
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    return Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => $estado,
    ]);
}

test('puedeCompletarConPago solo si puede gestionar la cita, puede transicionar a completada y crear pagos', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $citaEnAtencion = crearCitaDePrueba($negocio, $profesional, EstadoCitaValor::EnAtencion);
    $citaPendiente = crearCitaDePrueba($negocio, $profesional, EstadoCitaValor::Pendiente);

    $servicio = app(ServicioAccionesCita::class);

    expect($servicio->puedeCompletarConPago($citaEnAtencion, $dueno))->toBeTrue();
    expect($servicio->puedeCompletarConPago($citaPendiente, $dueno))->toBeFalse();
});

test('destinosPosiblesDeCambioDeEstado excluye reprogramada y completada', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $cita = crearCitaDePrueba($negocio, $profesional, EstadoCitaValor::Pendiente);

    $destinos = app(ServicioAccionesCita::class)->destinosPosiblesDeCambioDeEstado($cita);

    expect($destinos)->not->toContain(EstadoCitaValor::Reprogramada);
    expect($destinos)->not->toContain(EstadoCitaValor::Completada);
    expect($destinos)->toContain(EstadoCitaValor::Confirmada);
});

test('puedeCancelar es falso para una cita en un estado final', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $citaCancelada = crearCitaDePrueba($negocio, $profesional, EstadoCitaValor::Cancelada);
    $citaConfirmada = crearCitaDePrueba($negocio, $profesional, EstadoCitaValor::Confirmada);

    $servicio = app(ServicioAccionesCita::class);

    expect($servicio->puedeCancelar($citaCancelada, $dueno))->toBeFalse();
    expect($servicio->puedeCancelar($citaConfirmada, $dueno))->toBeTrue();
});

test('puedeAnularPago requiere un pago activo y el permiso de gestionar pagos', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $cita = crearCitaDePrueba($negocio, $profesional, EstadoCitaValor::Completada);

    $servicio = app(ServicioAccionesCita::class);

    expect($servicio->puedeAnularPago($cita, $dueno))->toBeFalse();

    Pago::factory()->create(['negocio_id' => $negocio->id, 'cita_id' => $cita->id]);

    expect($servicio->puedeAnularPago($cita->refresh(), $dueno))->toBeTrue();
});

test('puedeForzarReprogramacion solo para quien gestiona todas las citas', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $servicio = app(ServicioAccionesCita::class);

    expect($servicio->puedeForzarReprogramacion($dueno))->toBeTrue();
    expect($servicio->puedeForzarReprogramacion($profesionalUsuario))->toBeFalse();

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);
    expect($dueno->can(Permiso::GestionarCitasTodas->value))->toBeTrue();
});
