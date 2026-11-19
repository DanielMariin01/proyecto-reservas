<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Profesional;

test('un dueno entra a la raiz del panel y cae en el panel del dia', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}");

    $respuesta->assertRedirect(route('app.panel-del-dia', $negocio));
});

test('recepcion entra a la raiz del panel y cae en el panel del dia', function (): void {
    $negocio = Negocio::factory()->create();
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);

    $respuesta = $this->actingAs($recepcion)->get("/app/{$negocio->slug}");

    $respuesta->assertRedirect(route('app.panel-del-dia', $negocio));
});

test('un profesional entra a la raiz del panel y cae en sus citas de hoy', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $usuarioProfesional->id]);

    $respuesta = $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}");

    $respuesta->assertRedirect(route('app.mis-citas-hoy', $negocio));
});

test('un dueno no puede ver el panel de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $respuesta = $this->actingAs($duenoA)->get("/app/{$negocioB->slug}");

    $respuesta->assertNotFound();
});

test('un negocio suspendido muestra la pantalla de suspension', function (): void {
    $negocio = Negocio::factory()->suspendido()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}");

    $respuesta->assertForbidden();
});

test('sin sesion se redirige a ingresar', function (): void {
    $negocio = Negocio::factory()->create();

    $respuesta = $this->get("/app/{$negocio->slug}");

    $respuesta->assertRedirect(route('login'));
});
