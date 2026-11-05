<?php

declare(strict_types=1);

use App\Actions\CambiarVisibilidadResena;
use App\Enums\EstadoResenaValor;
use App\Enums\RolNegocio;
use App\Events\ResenaCambioDeVisibilidad;
use App\Models\Negocio;
use App\Models\Resena;
use Illuminate\Support\Facades\Event;

test('ocultar una reseña no la borra', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $resena = Resena::factory()->create(['negocio_id' => $negocio->id]);

    app(CambiarVisibilidadResena::class)->ejecutar($resena, EstadoResenaValor::Oculta, $dueno);

    $resena->refresh();
    expect($resena->estado)->toBe(EstadoResenaValor::Oculta);
    expect($resena->oculta_en)->not->toBeNull();
    expect($resena->oculta_por_id)->toBe($dueno->id);
    expect(Resena::query()->whereKey($resena->id)->exists())->toBeTrue();
});

test('se puede volver a publicar una reseña oculta', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $resena = Resena::factory()->oculta()->create(['negocio_id' => $negocio->id]);

    app(CambiarVisibilidadResena::class)->ejecutar($resena, EstadoResenaValor::Publicada, $dueno);

    $resena->refresh();
    expect($resena->estado)->toBe(EstadoResenaValor::Publicada);
    expect($resena->oculta_en)->toBeNull();
    expect($resena->oculta_por_id)->toBeNull();
});

test('cambiar la visibilidad dispara el evento ResenaCambioDeVisibilidad', function (): void {
    Event::fake();
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $resena = Resena::factory()->create(['negocio_id' => $negocio->id]);

    app(CambiarVisibilidadResena::class)->ejecutar($resena, EstadoResenaValor::Oculta, $dueno);

    Event::assertDispatched(ResenaCambioDeVisibilidad::class);
});
