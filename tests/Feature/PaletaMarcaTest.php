<?php

declare(strict_types=1);

use App\Filament\Resources\PaletaMarcas\Pages\CreatePaletaMarca;
use App\Models\PaletaMarca;
use App\Models\Usuario;
use App\Services\CalculadoraContraste;
use Database\Seeders\PaletasMarcaSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('el super admin crea una paleta de marca cuyo color primario cumple AA', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(CreatePaletaMarca::class)
        ->fillForm([
            'nombre' => 'Color Spa',
            'color_primario' => '#1B263B',
            'color_secundario' => '#415A77',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(PaletaMarca::query()->where('nombre', 'Color Spa')->exists())->toBeTrue();
});

test('una paleta cuyo color primario no cumple AA con texto blanco no se guarda', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($superAdmin)
        ->test(CreatePaletaMarca::class)
        ->fillForm([
            'nombre' => 'Color Pastel',
            'color_primario' => '#F6B8D0',
            'color_secundario' => '#F6B8D0',
        ])
        ->call('create')
        ->assertHasFormErrors(['color_primario']);

    expect(PaletaMarca::query()->where('nombre', 'Color Pastel')->exists())->toBeFalse();
});

test('el seeder crea las 2 paletas iniciales y ambas cumplen AA', function (): void {
    (new PaletasMarcaSeeder)->run();

    $barberia = PaletaMarca::query()->where('nombre', 'Color Barbería')->firstOrFail();
    $unas = PaletaMarca::query()->where('nombre', 'Color Uñas')->firstOrFail();

    expect(app(CalculadoraContraste::class)->cumpleAaConBlanco($barberia->color_primario))->toBeTrue();
    expect(app(CalculadoraContraste::class)->cumpleAaConBlanco($unas->color_primario))->toBeTrue();
});
