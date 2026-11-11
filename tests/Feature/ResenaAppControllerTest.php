<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\EstadoResenaValor;
use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Resena;

test('una recepcion sin el permiso de gestionar resenas no puede ver la pagina', function (): void {
    $negocio = Negocio::factory()->create();
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);

    $this->actingAs($recepcion)->get("/app/{$negocio->slug}/resenas")->assertForbidden();
    $this->actingAs($recepcion)->getJson("/app/{$negocio->slug}/api/resenas")->assertForbidden();
});

test('el dueno ve la pagina de resenas', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/resenas")->assertOk()->assertSee('Reseñas');
});

test('el dueno ve todas las resenas del negocio en la api', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesionalUno = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Uno'));
    $profesionalDos = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Dos'));
    Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesionalUno->id]);
    Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesionalDos->id]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/resenas");

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(2);
});

test('un profesional sin el permiso de gestionar resenas solo ve las suyas', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesionalPropio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesionalPropio->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));

    $resenaPropia = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesionalPropio->id]);
    Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $otroProfesional->id]);

    $respuesta = $this->actingAs($usuarioProfesional)->getJson("/app/{$negocio->slug}/api/resenas");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['id'])->toBe($resenaPropia->id);
});

test('un profesional no puede responder ni ocultar la resena de otro profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesionalPropio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesionalPropio->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));
    $resenaAjena = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $otroProfesional->id]);

    $this->actingAs($usuarioProfesional)->postJson("/app/{$negocio->slug}/api/resenas/{$resenaAjena->id}/responder", [
        'respuesta' => 'Gracias',
    ])->assertForbidden();
    $this->actingAs($usuarioProfesional)->postJson("/app/{$negocio->slug}/api/resenas/{$resenaAjena->id}/ocultar")->assertForbidden();
});

test('responder una resena guarda la respuesta', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $resena = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/resenas/{$resena->id}/responder", [
        'respuesta' => 'Gracias por tu visita',
    ]);

    $respuesta->assertOk();
    expect($resena->refresh()->respuesta_texto)->toBe('Gracias por tu visita');
});

test('ocultar y volver a publicar una resena cambia su estado', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $resena = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id]);

    $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/resenas/{$resena->id}/ocultar")->assertOk();
    expect($resena->refresh()->estado)->toBe(EstadoResenaValor::Oculta);

    $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/resenas/{$resena->id}/mostrar")->assertOk();
    expect($resena->refresh()->estado)->toBe(EstadoResenaValor::Publicada);
});
