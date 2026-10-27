<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Models\Servicio;
use Filament\Facades\Filament;

test('el scope global solo filtra cuando hay un tenant activo de Filament', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    Servicio::factory()->create(['negocio_id' => $negocioA->id]);
    Servicio::factory()->create(['negocio_id' => $negocioB->id]);

    expect(Servicio::count())->toBe(2);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocioA, isQuiet: true);

    expect(Servicio::count())->toBe(1);
    expect(Servicio::first()->negocio_id)->toBe($negocioA->id);
});

test('reordenar un servicio no cambia su negocio ni su categoria', function (): void {
    $negocio = Negocio::factory()->create();
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'orden' => 0]);

    $servicio->update(['orden' => 5]);
    $servicio->refresh();

    expect($servicio->orden)->toBe(5);
    expect($servicio->negocio_id)->toBe($negocio->id);
});
