<?php

declare(strict_types=1);

use App\Enums\FranjaHoraria;
use Carbon\CarbonImmutable;

test('paraHora clasifica segun la hora local del negocio, no la del servidor', function (): void {
    // 15:30 UTC es 10:30 a. m. en Bogota (UTC-5): Mañana, no Tarde.
    $momento = CarbonImmutable::parse('2026-11-03 15:30:00', 'UTC');

    expect(FranjaHoraria::paraHora($momento, 'America/Bogota'))->toBe(FranjaHoraria::Manana);
});

test('paraHora ubica cada hora limite en la franja que empieza ahi', function (): void {
    $fecha = fn (string $hora) => CarbonImmutable::parse("2026-11-03 {$hora}:00", 'America/Bogota');

    expect(FranjaHoraria::paraHora($fecha('06:00'), 'America/Bogota'))->toBe(FranjaHoraria::Manana);
    expect(FranjaHoraria::paraHora($fecha('11:59'), 'America/Bogota'))->toBe(FranjaHoraria::Manana);
    expect(FranjaHoraria::paraHora($fecha('12:00'), 'America/Bogota'))->toBe(FranjaHoraria::Tarde);
    expect(FranjaHoraria::paraHora($fecha('17:59'), 'America/Bogota'))->toBe(FranjaHoraria::Tarde);
    expect(FranjaHoraria::paraHora($fecha('18:00'), 'America/Bogota'))->toBe(FranjaHoraria::Noche);
    expect(FranjaHoraria::paraHora($fecha('21:59'), 'America/Bogota'))->toBe(FranjaHoraria::Noche);
});

test('paraHora cae en Noche para una hora fuera de las 3 franjas', function (): void {
    $madrugada = CarbonImmutable::parse('2026-11-03 02:00:00', 'America/Bogota');

    expect(FranjaHoraria::paraHora($madrugada, 'America/Bogota'))->toBe(FranjaHoraria::Noche);
});

test('rangoUtc devuelve el rango de la franja convertido a UTC', function (): void {
    $fechaLocal = CarbonImmutable::parse('2026-11-03 00:00:00', 'America/Bogota');

    [$inicio, $fin] = FranjaHoraria::Tarde->rangoUtc($fechaLocal);

    expect($inicio->toIso8601String())->toBe('2026-11-03T17:00:00+00:00');
    expect($fin->toIso8601String())->toBe('2026-11-03T23:00:00+00:00');
});
