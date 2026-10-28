<?php

declare(strict_types=1);

use App\Actions\CrearBloqueoRapido;
use App\DatosTransferencia\DatosBloqueoRapido;
use App\Enums\TipoBloqueo;
use App\Models\Negocio;
use App\Models\Profesional;
use Carbon\CarbonImmutable;

test('crea un bloqueo convirtiendo la hora de pared del negocio a UTC', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $fecha = CarbonImmutable::parse('2026-11-10', 'America/Bogota');

    $bloqueo = app(CrearBloqueoRapido::class)->ejecutar(new DatosBloqueoRapido(
        profesional: $profesional,
        fecha: $fecha,
        horaInicio: '15:00',
        horaFin: '17:00',
        motivo: 'No atiendo hoy de 3 a 5',
    ));

    expect($bloqueo->tipo)->toBe(TipoBloqueo::Bloqueo);
    expect($bloqueo->negocio_id)->toBe($negocio->id);
    expect($bloqueo->profesional_id)->toBe($profesional->id);
    expect($bloqueo->motivo)->toBe('No atiendo hoy de 3 a 5');
    // 15:00 Bogota (UTC-5) = 20:00 UTC.
    expect($bloqueo->inicio->utc()->format('H:i'))->toBe('20:00');
    expect($bloqueo->fin->utc()->format('H:i'))->toBe('22:00');
});
