<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\DiaSemana;
use App\Enums\RolNegocio;
use App\Models\Bloqueo;
use App\Models\Cita;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;

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

test('el profesional obtiene su propio horario agrupado por dia, sin ningun id en la url', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::Lunes,
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $respuesta = $this->actingAs($usuarioProfesional)->getJson("/app/{$negocio->slug}/api/mi-horario/horario");

    $respuesta->assertOk();
    expect($respuesta->json('dias.'.DiaSemana::Lunes->value.'.disponible'))->toBeTrue();
    expect($respuesta->json('dias.'.DiaSemana::Lunes->value.'.rangos'))->toHaveCount(1);
});

test('el profesional guarda su propio horario semanal', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    $respuesta = $this->actingAs($usuarioProfesional)->putJson("/app/{$negocio->slug}/api/mi-horario/horario", [
        'dias' => [
            DiaSemana::Lunes->value => [
                'disponible' => true,
                'rangos' => [
                    ['hora_inicio' => '09:00', 'hora_fin' => '13:00'],
                ],
            ],
        ],
    ]);

    $respuesta->assertOk();
    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(1);
});

test('un profesional sin profesional vinculado no puede ver ni guardar ningun horario propio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/mi-horario/horario")->assertForbidden();
    $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/mi-horario/horario", [
        'dias' => [DiaSemana::Lunes->value => ['disponible' => false, 'rangos' => []]],
    ])->assertForbidden();
});

test('guardar el horario propio con rangos superpuestos no cambia nada ni toca el de otro profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($usuarioProfesional)->putJson("/app/{$negocio->slug}/api/mi-horario/horario", [
        'dias' => [
            DiaSemana::Lunes->value => [
                'disponible' => true,
                'rangos' => [
                    ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                    ['hora_inicio' => '11:00', 'hora_fin' => '13:00'],
                ],
            ],
        ],
    ]);

    $respuesta->assertStatus(422);
    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(0);
    expect(HorarioTrabajo::query()->where('profesional_id', $otroProfesional->id)->count())->toBe(0);
});

test('un profesional no puede tocar el horario de otro ni pasando el id de otro profesional en el cuerpo', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($usuarioProfesional)->putJson("/app/{$negocio->slug}/api/mi-horario/horario", [
        'profesional_id' => $otroProfesional->id,
        'dias' => [
            DiaSemana::Lunes->value => [
                'disponible' => true,
                'rangos' => [['hora_inicio' => '09:00', 'hora_fin' => '10:00']],
            ],
        ],
    ])->assertOk();

    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(1);
    expect(HorarioTrabajo::query()->where('profesional_id', $otroProfesional->id)->count())->toBe(0);
});

test('el profesional ve su propia columna de agenda, con sus citas y acciones', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(10, 30)->utc(),
    ]);

    $respuesta = $this->actingAs($usuarioProfesional)->getJson("/app/{$negocio->slug}/api/mi-horario/agenda?fecha=2026-11-03");

    $respuesta->assertOk();
    expect($respuesta->json('columna.profesionalId'))->toBe($profesional->id);
    expect($respuesta->json('columna.citas'))->toHaveCount(1);

    CarbonImmutable::setTestNow();
});

test('la agenda propia nunca incluye citas de otro profesional del mismo negocio', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'servicio_id' => $servicio->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(10, 30)->utc(),
    ]);

    $respuesta = $this->actingAs($usuarioProfesional)->getJson("/app/{$negocio->slug}/api/mi-horario/agenda?fecha=2026-11-03");

    $respuesta->assertOk();
    expect($respuesta->json('columna.profesionalId'))->toBe($profesional->id);
    expect($respuesta->json('columna.citas'))->toHaveCount(0);

    CarbonImmutable::setTestNow();
});

test('un profesional no puede completar con pago la cita de otro, incluso pasando por la ruta compartida de acciones', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'precio' => 20000]);

    $citaDeOtro = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'servicio_id' => $servicio->id,
    ]);

    $this->actingAs($usuarioProfesional)->postJson("/app/{$negocio->slug}/api/citas/{$citaDeOtro->id}/cambiar-estado", [
        'estado' => 'en_atencion',
    ])->assertForbidden();
});

test('el acceso a Mi agenda exige un profesional vinculado', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/mi-agenda")->assertForbidden();
});
