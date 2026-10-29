<?php

declare(strict_types=1);

use App\Services\CalculadoraContraste;

function calculadoraContraste(): CalculadoraContraste
{
    return new CalculadoraContraste;
}

test('tinta sobre blanco cumple el contraste AA de la skill (10,4:1)', function (): void {
    expect(calculadoraContraste()->contrasteConBlanco('#3E4043'))->toBeGreaterThan(10.0);
});

test('primario sobre blanco cumple el minimo de texto normal (5,0:1)', function (): void {
    expect(calculadoraContraste()->cumpleAaConBlanco('#673DE6'))->toBeTrue();
});

test('un rosa claro no cumple AA con texto blanco encima', function (): void {
    expect(calculadoraContraste()->cumpleAaConBlanco('#F6B8D0'))->toBeFalse();
});

test('las paletas iniciales de la libreria de marca cumplen AA', function (): void {
    expect(calculadoraContraste()->cumpleAaConBlanco('#1B263B'))->toBeTrue(); // Color Barbería
    expect(calculadoraContraste()->cumpleAaConBlanco('#C2185B'))->toBeTrue(); // Color Uñas
});

test('celeste sobre tinta y gris claro sobre tinta cumplen AA para texto secundario en fondo oscuro', function (): void {
    expect(calculadoraContraste()->contrasteEntre('#E1EEFF', '#3E4043'))->toBeGreaterThan(4.5);
    expect(calculadoraContraste()->contrasteEntre('#EAEAEA', '#3E4043'))->toBeGreaterThan(4.5);
});
