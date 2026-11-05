<?php

declare(strict_types=1);

use App\Actions\GenerarCodigoDeReferencia;

test('genera siempre el mismo codigo para el mismo id de peticion', function (): void {
    $accion = new GenerarCodigoDeReferencia;
    $idPeticion = '550e8400-e29b-41d4-a716-446655440000';

    expect($accion->ejecutar($idPeticion))->toBe($accion->ejecutar($idPeticion));
});

test('el codigo tiene el formato REF- seguido de 8 caracteres en mayuscula', function (): void {
    $accion = new GenerarCodigoDeReferencia;

    expect($accion->ejecutar('550e8400-e29b-41d4-a716-446655440000'))->toMatch('/^REF-[A-F0-9]{8}$/');
});

test('ids de peticion distintos dan codigos distintos', function (): void {
    $accion = new GenerarCodigoDeReferencia;

    $codigoUno = $accion->ejecutar('550e8400-e29b-41d4-a716-446655440000');
    $codigoDos = $accion->ejecutar('111e8400-e29b-41d4-a716-446655440000');

    expect($codigoUno)->not->toBe($codigoDos);
});
