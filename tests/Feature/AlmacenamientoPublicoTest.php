<?php

declare(strict_types=1);

use App\Support\AlmacenamientoPublico;
use Illuminate\Support\Facades\Storage;

test('deja la ruta relativa intacta', function (): void {
    expect(AlmacenamientoPublico::aRuta('servicios/corte.jpg'))->toBe('servicios/corte.jpg');
});

test('recorta la url completa del disco publico a la ruta relativa', function (): void {
    $url = Storage::disk('public')->url('servicios/corte.jpg');

    expect(AlmacenamientoPublico::aRuta($url))->toBe('servicios/corte.jpg');
});

test('deja null y vacio intactos', function (): void {
    expect(AlmacenamientoPublico::aRuta(null))->toBeNull();
    expect(AlmacenamientoPublico::aRuta(''))->toBe('');
});
