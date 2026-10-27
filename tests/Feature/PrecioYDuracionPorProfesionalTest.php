<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;

test('sin override, el profesional usa el precio y duracion del servicio', function (): void {
    $negocio = Negocio::factory()->create();
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'precio' => 30000, 'duracion_minutos' => 45]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $profesional->servicios()->attach($servicio->id);

    expect($profesional->precioParaServicio($servicio))->toBe(30000);
    expect($profesional->duracionMinutosParaServicio($servicio))->toBe(45);
});

test('con override, el profesional usa su propio precio y duracion', function (): void {
    $negocio = Negocio::factory()->create();
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'precio' => 30000, 'duracion_minutos' => 45]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $profesional->servicios()->attach($servicio->id, ['precio' => 50000, 'duracion_minutos' => 60]);

    expect($profesional->precioParaServicio($servicio))->toBe(50000);
    expect($profesional->duracionMinutosParaServicio($servicio))->toBe(60);
});

test('un profesional que no presta el servicio usa el valor del servicio como respaldo', function (): void {
    $negocio = Negocio::factory()->create();
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'precio' => 30000, 'duracion_minutos' => 45]);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    expect($profesional->precioParaServicio($servicio))->toBe(30000);
    expect($profesional->duracionMinutosParaServicio($servicio))->toBe(45);
});
