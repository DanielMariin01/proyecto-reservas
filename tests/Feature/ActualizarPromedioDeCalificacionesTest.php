<?php

declare(strict_types=1);

use App\Actions\CambiarVisibilidadResena;
use App\Enums\EstadoResenaValor;
use App\Enums\RolNegocio;
use App\Events\ResenaCreada;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Resena;

test('un negocio y un profesional sin reseñas no tienen promedio', function (): void {
    $negocio = Negocio::factory()->create()->refresh();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id])->refresh();

    expect($negocio->promedio_calificacion)->toBeNull();
    expect($negocio->total_resenas)->toBe(0);
    expect($profesional->promedio_calificacion)->toBeNull();
    expect($profesional->total_resenas)->toBe(0);
});

test('crear una reseña recalcula el promedio del negocio y del profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $resenaUno = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'calificacion' => 5]);
    ResenaCreada::dispatch($resenaUno);

    $resenaDos = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'calificacion' => 3]);
    ResenaCreada::dispatch($resenaDos);

    $negocio->refresh();
    $profesional->refresh();

    expect($negocio->promedio_calificacion)->toBe(4.0);
    expect($negocio->total_resenas)->toBe(2);
    expect($profesional->promedio_calificacion)->toBe(4.0);
    expect($profesional->total_resenas)->toBe(2);
});

test('ocultar una reseña la saca del promedio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $resenaBuena = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'calificacion' => 5]);
    ResenaCreada::dispatch($resenaBuena);

    $resenaMala = Resena::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'calificacion' => 1]);
    ResenaCreada::dispatch($resenaMala);

    app(CambiarVisibilidadResena::class)->ejecutar($resenaMala, EstadoResenaValor::Oculta, $dueno);

    $negocio->refresh();
    expect($negocio->promedio_calificacion)->toBe(5.0);
    expect($negocio->total_resenas)->toBe(1);
});
