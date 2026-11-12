<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Models\Bloqueo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;

test('un usuario sin profesional vinculado no puede ver Mi Horario', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/mi-horario")->assertForbidden();
    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/mi-horario")->assertForbidden();
});

test('un profesional vinculado a su registro puede ver Mi Horario', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}/mi-horario")->assertOk();
});

test('la api de mi horario solo devuelve los horarios y bloqueos del profesional actual', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    Bloqueo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'fin' => now()->addDay(),
    ]);
    Bloqueo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'fin' => now()->addDay(),
    ]);

    $respuesta = $this->actingAs($usuarioProfesional)->getJson("/app/{$negocio->slug}/api/mi-horario");

    $respuesta->assertOk();
    expect($respuesta->json('bloqueos'))->toHaveCount(1);
});

test('el formulario de bloqueo rapido crea un bloqueo convertido a UTC', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    $respuesta = $this->actingAs($usuarioProfesional)->postJson("/app/{$negocio->slug}/api/mi-horario/bloqueos", [
        'fecha' => '2026-11-10',
        'hora_inicio' => '15:00',
        'hora_fin' => '17:00',
        'motivo' => 'No atiendo hoy de 3 a 5',
    ]);

    $respuesta->assertCreated();

    $this->assertDatabaseHas('bloqueos', [
        'profesional_id' => $profesional->id,
        'motivo' => 'No atiendo hoy de 3 a 5',
        'inicio' => '2026-11-10 20:00:00',
        'fin' => '2026-11-10 22:00:00',
    ]);
});

test('un profesional no puede eliminar un bloqueo de otro profesional aunque adivine el id', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    $otroProfesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $bloqueoDeOtro = Bloqueo::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $otroProfesional->id]);

    $this->actingAs($usuarioProfesional)->deleteJson("/app/{$negocio->slug}/api/mi-horario/bloqueos/{$bloqueoDeOtro->id}")->assertOk();

    expect(Bloqueo::query()->find($bloqueoDeOtro->id))->not->toBeNull();
});

test('la vista previa de disponibilidad devuelve las franjas del profesional actual', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($usuarioProfesional)->getJson(
        "/app/{$negocio->slug}/api/mi-horario/vista-previa-disponibilidad?servicio_id={$servicio->id}&fecha=2026-11-12",
    );

    $respuesta->assertOk();
    expect($respuesta->json('franjas'))->toBeArray();
});
