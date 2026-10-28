<?php

declare(strict_types=1);

use App\Support\Fecha;
use Carbon\CarbonImmutable;

test('formatea fechas en el estilo colombiano del proyecto', function (): void {
    $martes = CarbonImmutable::parse('2026-10-27 10:00:00', 'America/Bogota');

    expect(Fecha::formatear($martes, 'America/Bogota'))->toBe('mar 27 oct');
});

test('convierte una fecha en UTC a la zona del negocio antes de formatear', function (): void {
    // 2026-10-28 02:00 UTC es 2026-10-27 21:00 en Bogota (UTC-5): sigue siendo martes 27.
    $enUtc = CarbonImmutable::parse('2026-10-28 02:00:00', 'UTC');

    expect(Fecha::formatear($enUtc, 'America/Bogota'))->toBe('mar 27 oct');
});
