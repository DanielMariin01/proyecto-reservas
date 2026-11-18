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

test('el dueno y la recepcion pueden gestionar citas de todas, un profesional no', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    expect($this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/usuario-actual")->json('puedeGestionarCitasTodas'))->toBeTrue();
    expect($this->actingAs($recepcion)->getJson("/app/{$negocio->slug}/api/usuario-actual")->json('puedeGestionarCitasTodas'))->toBeTrue();
    expect($this->actingAs($profesional)->getJson("/app/{$negocio->slug}/api/usuario-actual")->json('puedeGestionarCitasTodas'))->toBeFalse();
});

test('solo el dueno puede gestionar el catalogo', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    expect($this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/usuario-actual")->json('puedeGestionarCatalogo'))->toBeTrue();
    expect($this->actingAs($profesional)->getJson("/app/{$negocio->slug}/api/usuario-actual")->json('puedeGestionarCatalogo'))->toBeFalse();
});

test('el dueno solo puede gestionar la pagina web si el negocio lo permite', function (): void {
    $negocioHabilitado = Negocio::factory()->create(['permite_editar_pagina' => true]);
    $duenoHabilitado = crearUsuarioConRol($negocioHabilitado, RolNegocio::Dueno);

    $negocioDeshabilitado = Negocio::factory()->create(['permite_editar_pagina' => false]);
    $duenoDeshabilitado = crearUsuarioConRol($negocioDeshabilitado, RolNegocio::Dueno);

    expect(
        $this->actingAs($duenoHabilitado)->getJson("/app/{$negocioHabilitado->slug}/api/usuario-actual")->json('puedeGestionarNegocio'),
    )->toBeTrue();
    expect(
        $this->actingAs($duenoDeshabilitado)->getJson("/app/{$negocioDeshabilitado->slug}/api/usuario-actual")->json('puedeGestionarNegocio'),
    )->toBeFalse();
});

test('solo el dueno puede gestionar resenas y usuarios', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $respuestaDueno = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/usuario-actual");
    expect($respuestaDueno->json('puedeGestionarResenas'))->toBeTrue();
    expect($respuestaDueno->json('puedeGestionarUsuarios'))->toBeTrue();

    $respuestaProfesional = $this->actingAs($profesional)->getJson("/app/{$negocio->slug}/api/usuario-actual");
    expect($respuestaProfesional->json('puedeGestionarResenas'))->toBeFalse();
    expect($respuestaProfesional->json('puedeGestionarUsuarios'))->toBeFalse();
});
