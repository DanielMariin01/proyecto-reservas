<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Servicio;

test('un profesional puede registrar el pago de su propia cita pero no de la de otro', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesionalPropio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesionalPropio->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaPropia = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesionalPropio->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);
    $citaAjena = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $otroProfesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);

    expect($usuarioProfesional->can('create', [Pago::class, $citaPropia]))->toBeTrue();
    expect($usuarioProfesional->can('create', [Pago::class, $citaAjena]))->toBeFalse();
});

test('el dueno y la recepcion pueden registrar el pago de cualquier cita', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);

    expect($dueno->can('create', [Pago::class, $cita]))->toBeTrue();
    expect($recepcion->can('create', [Pago::class, $cita]))->toBeTrue();
});

test('un profesional no puede anular un pago, ni siquiera el de su propia cita', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);
    $pago = Pago::factory()->create(['negocio_id' => $negocio->id, 'cita_id' => $cita->id]);

    expect($usuarioProfesional->can('anular', $pago))->toBeFalse();
});

test('el dueno puede anular un pago', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);
    $pago = Pago::factory()->create(['negocio_id' => $negocio->id, 'cita_id' => $cita->id]);

    expect($dueno->can('anular', $pago))->toBeTrue();
});
