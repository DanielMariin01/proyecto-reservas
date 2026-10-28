<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Filament\App\Pages\MiHorario;
use App\Models\Negocio;
use App\Models\Profesional;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('un usuario sin profesional vinculado no puede ver Mi Horario', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/mi-horario");

    $respuesta->assertForbidden();
});

test('un profesional vinculado a su registro puede ver Mi Horario y crear un bloqueo rapido', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(
        negocio: $negocio,
        nombre: 'Japo',
    ));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    $respuesta = $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}/mi-horario");

    $respuesta->assertOk();

    expect(Profesional::query()->whereKey($profesional->id)->first())
        ->usuario_id->toBe($usuarioProfesional->id);
});

test('el formulario de bloqueo rapido crea un bloqueo convertido a UTC', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(
        negocio: $negocio,
        nombre: 'Japo',
    ));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($usuarioProfesional)
        ->test(MiHorario::class)
        ->fillForm([
            'fecha' => '2026-11-10',
            'hora_inicio' => '15:00',
            'hora_fin' => '17:00',
            'motivo' => 'No atiendo hoy de 3 a 5',
        ])
        ->call('crearBloqueo')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('bloqueos', [
        'profesional_id' => $profesional->id,
        'motivo' => 'No atiendo hoy de 3 a 5',
        'inicio' => '2026-11-10 20:00:00',
        'fin' => '2026-11-10 22:00:00',
    ]);
});

test('el dueno puede ver el perfil de un profesional con sus horarios y bloqueos', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(
        negocio: $negocio,
        nombre: 'Japo',
    ));

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/profesionales/{$profesional->id}");

    $respuesta->assertOk();
});
