<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Resena;
use App\Negocios\ContextoNegocioActual;

test('un profesional puede ver solo sus propias reseñas', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesionalPropio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesionalPropio->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));

    $resenaPropia = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesionalPropio->id]);
    $resenaAjena = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $otroProfesional->id]);

    expect($usuarioProfesional->can('view', $resenaPropia))->toBeTrue();
    expect($usuarioProfesional->can('update', $resenaPropia))->toBeTrue();
    expect($usuarioProfesional->can('view', $resenaAjena))->toBeFalse();
    expect($usuarioProfesional->can('update', $resenaAjena))->toBeFalse();
});

test('el dueno puede ver y gestionar todas las reseñas del negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));

    $resena = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id]);

    expect($dueno->can('view', $resena))->toBeTrue();
    expect($dueno->can('update', $resena))->toBeTrue();
});

test('la recepcion sin el permiso de gestionar reseñas no puede verlas ni gestionarlas', function (): void {
    $negocio = Negocio::factory()->create();
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));

    $resena = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id]);

    expect($recepcion->can('view', $resena))->toBeFalse();
    expect($recepcion->can('update', $resena))->toBeFalse();
});

test('el alcance global del tenant oculta las reseñas de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();

    $resenaA = Resena::factory()->create(['negocio_id' => $negocioA->id]);
    $resenaB = Resena::factory()->create(['negocio_id' => $negocioB->id]);

    app(ContextoNegocioActual::class)->fijar($negocioA);

    $idsVisibles = Resena::query()->pluck('id');

    expect($idsVisibles)->toContain($resenaA->id);
    expect($idsVisibles)->not->toContain($resenaB->id);
});
