<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\DiaSemana;
use App\Enums\RolNegocio;
use App\Filament\App\Resources\Profesionales\Pages\ViewProfesional;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('el dueno puede editar el horario semanal desde el perfil del profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(
        negocio: $negocio,
        nombre: 'Japo',
    ));

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::Martes,
        'hora_inicio' => '08:00:00',
        'hora_fin' => '09:00:00',
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(ViewProfesional::class, ['record' => $profesional->getRouteKey()])
        ->mountAction('editarHorarioSemanal')
        ->setActionData([
            'dias' => [
                (string) DiaSemana::Lunes->value => ['disponible' => true, 'rangos' => [
                    ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                    ['hora_inicio' => '14:00', 'hora_fin' => '18:00'],
                ]],
                (string) DiaSemana::Martes->value => ['disponible' => false, 'rangos' => []],
            ],
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    $horarios = HorarioTrabajo::query()->where('profesional_id', $profesional->id)->orderBy('hora_inicio')->get();

    expect($horarios)->toHaveCount(2);
    expect($horarios[0]->dia_semana)->toBe(DiaSemana::Lunes);
    expect($horarios[0]->hora_inicio)->toBe('10:00:00');
    expect($horarios[1]->hora_inicio)->toBe('14:00:00');
});

test('el editor de horario rechaza rangos superpuestos y no cierra el modal', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(
        negocio: $negocio,
        nombre: 'Japo',
    ));

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(ViewProfesional::class, ['record' => $profesional->getRouteKey()])
        ->mountAction('editarHorarioSemanal')
        ->setActionData([
            'dias' => [
                (string) DiaSemana::Lunes->value => ['disponible' => true, 'rangos' => [
                    ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                    ['hora_inicio' => '11:00', 'hora_fin' => '13:00'],
                ]],
            ],
        ])
        ->callMountedAction();

    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(0);
});
