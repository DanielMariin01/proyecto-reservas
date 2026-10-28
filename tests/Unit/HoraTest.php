<?php

declare(strict_types=1);

use App\Support\Hora;
use Carbon\CarbonImmutable;

test('formatea horas en el estilo colombiano del proyecto', function (): void {
    $manana = CarbonImmutable::parse('2026-10-27 10:00:00', 'America/Bogota');
    $tarde = CarbonImmutable::parse('2026-10-27 15:30:00', 'America/Bogota');
    $medianoche = CarbonImmutable::parse('2026-10-27 00:00:00', 'America/Bogota');

    expect(Hora::formatear($manana, 'America/Bogota'))->toBe('10:00 a. m.');
    expect(Hora::formatear($tarde, 'America/Bogota'))->toBe('3:30 p. m.');
    expect(Hora::formatear($medianoche, 'America/Bogota'))->toBe('12:00 a. m.');
});

test('convierte una hora en UTC a la zona del negocio antes de formatear', function (): void {
    $enUtc = CarbonImmutable::parse('2026-10-27 15:00:00', 'UTC');

    expect(Hora::formatear($enUtc, 'America/Bogota'))->toBe('10:00 a. m.');
});
