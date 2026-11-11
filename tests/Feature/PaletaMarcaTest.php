<?php

declare(strict_types=1);

use App\Models\PaletaMarca;
use App\Models\Usuario;
use App\Services\CalculadoraContraste;
use Database\Seeders\PaletasMarcaSeeder;

test('el super admin crea una paleta de marca cuyo color primario cumple AA', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson('/admin/api/paleta-marcas', [
        'nombre' => 'Color Spa',
        'color_primario' => '#1B263B',
        'color_secundario' => '#415A77',
    ]);

    $respuesta->assertCreated();
    expect(PaletaMarca::query()->where('nombre', 'Color Spa')->exists())->toBeTrue();
});

test('una paleta cuyo color primario no cumple AA con texto blanco no se guarda', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson('/admin/api/paleta-marcas', [
        'nombre' => 'Color Pastel',
        'color_primario' => '#F6B8D0',
        'color_secundario' => '#F6B8D0',
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['color_primario']);
    expect(PaletaMarca::query()->where('nombre', 'Color Pastel')->exists())->toBeFalse();
});

test('el seeder crea las 2 paletas iniciales y ambas cumplen AA', function (): void {
    (new PaletasMarcaSeeder)->run();

    $barberia = PaletaMarca::query()->where('nombre', 'Color Barbería')->firstOrFail();
    $unas = PaletaMarca::query()->where('nombre', 'Color Uñas')->firstOrFail();

    expect(app(CalculadoraContraste::class)->cumpleAaConBlanco($barberia->color_primario))->toBeTrue();
    expect(app(CalculadoraContraste::class)->cumpleAaConBlanco($unas->color_primario))->toBeTrue();
});
