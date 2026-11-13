<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Models\Negocio;

test('un dueno sin profesional vinculado no es profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/usuario-actual");

    $respuesta->assertOk();
    expect($respuesta->json('esProfesional'))->toBeFalse();
});

test('un dueno vinculado a un profesional si es profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $dueno->id]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/usuario-actual");

    $respuesta->assertOk();
    expect($respuesta->json('esProfesional'))->toBeTrue();
});

test('el dueno puede ver finanzas', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/usuario-actual");

    $respuesta->assertOk();
    expect($respuesta->json('puedeVerFinanzas'))->toBeTrue();
});

test('un profesional no puede ver finanzas', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $respuesta = $this->actingAs($profesional)->getJson("/app/{$negocio->slug}/api/usuario-actual");

    $respuesta->assertOk();
    expect($respuesta->json('puedeVerFinanzas'))->toBeFalse();
});
