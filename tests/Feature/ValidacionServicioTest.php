<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Filament\App\Resources\Servicios\Pages\CreateServicio;
use App\Models\Negocio;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('un servicio no acepta menos de 5 minutos de duracion', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(CreateServicio::class)
        ->fillForm([
            'nombre' => 'Corte rápido',
            'duracion_minutos' => 3,
            'precio' => 10000,
        ])
        ->call('create')
        ->assertHasFormErrors(['duracion_minutos']);
});

test('un servicio con 5 minutos o mas se guarda sin error', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(CreateServicio::class)
        ->fillForm([
            'nombre' => 'Corte rápido',
            'duracion_minutos' => 5,
            'precio' => 10000,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('servicios', [
        'negocio_id' => $negocio->id,
        'nombre' => 'Corte rápido',
    ]);
});
