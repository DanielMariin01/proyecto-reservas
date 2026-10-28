<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Servicio;

test('un profesional puede ver y actualizar solo sus propias citas', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesionalPropio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesionalPropio->update(['usuario_id' => $usuarioProfesional->id]);

    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaPropia = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesionalPropio->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
    ]);
    $citaAjena = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
    ]);

    expect($usuarioProfesional->can('view', $citaPropia))->toBeTrue();
    expect($usuarioProfesional->can('update', $citaPropia))->toBeTrue();
    expect($usuarioProfesional->can('view', $citaAjena))->toBeFalse();
    expect($usuarioProfesional->can('update', $citaAjena))->toBeFalse();
    expect($usuarioProfesional->can('create', Cita::class))->toBeFalse();
});

test('el dueno y la recepcion pueden ver y gestionar todas las citas del negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
    ]);

    expect($dueno->can('view', $cita))->toBeTrue();
    expect($dueno->can('update', $cita))->toBeTrue();
    expect($dueno->can('delete', $cita))->toBeTrue();
    expect($dueno->can('create', Cita::class))->toBeTrue();
    expect($recepcion->can('view', $cita))->toBeTrue();
    expect($recepcion->can('create', Cita::class))->toBeTrue();
});
