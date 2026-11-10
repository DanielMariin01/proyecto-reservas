<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Negocios\ContextoNegocioActual;

test('no hay negocio activo antes de fijar uno', function (): void {
    expect((new ContextoNegocioActual)->actual())->toBeNull();
});

test('fijar deja disponible el mismo negocio', function (): void {
    $negocio = new Negocio;
    $contexto = new ContextoNegocioActual;

    $contexto->fijar($negocio);

    expect($contexto->actual())->toBe($negocio);
});

test('limpiar vuelve a dejar el contexto sin negocio', function (): void {
    $contexto = new ContextoNegocioActual;
    $contexto->fijar(new Negocio);

    $contexto->limpiar();

    expect($contexto->actual())->toBeNull();
});
