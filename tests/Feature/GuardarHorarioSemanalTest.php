<?php

declare(strict_types=1);

use App\Actions\GuardarHorarioSemanal;
use App\DatosTransferencia\DatosHorarioSemanal;
use App\Enums\DiaSemana;
use App\Exceptions\RangosDeHorarioSuperpuestosException;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;

test('reemplaza todo el horario anterior del profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::Martes,
        'hora_inicio' => '08:00:00',
        'hora_fin' => '09:00:00',
    ]);

    app(GuardarHorarioSemanal::class)->ejecutar(new DatosHorarioSemanal(
        profesional: $profesional,
        rangosPorDia: [
            DiaSemana::Lunes->value => [
                ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                ['hora_inicio' => '14:00', 'hora_fin' => '18:00'],
            ],
        ],
    ));

    $horarios = HorarioTrabajo::query()->where('profesional_id', $profesional->id)->orderBy('hora_inicio')->get();

    expect($horarios)->toHaveCount(2);
    expect($horarios[0]->dia_semana)->toBe(DiaSemana::Lunes);
    expect($horarios[0]->hora_inicio)->toBe('10:00:00');
    expect($horarios[1]->hora_inicio)->toBe('14:00:00');
});

test('permite dos rangos separados el mismo dia (mañana y tarde)', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    app(GuardarHorarioSemanal::class)->ejecutar(new DatosHorarioSemanal(
        profesional: $profesional,
        rangosPorDia: [
            DiaSemana::Lunes->value => [
                ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                ['hora_inicio' => '14:00', 'hora_fin' => '18:00'],
            ],
        ],
    ));

    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(2);
});

test('rechaza rangos que se superponen el mismo dia y no guarda nada', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    $ejecutar = fn () => app(GuardarHorarioSemanal::class)->ejecutar(new DatosHorarioSemanal(
        profesional: $profesional,
        rangosPorDia: [
            DiaSemana::Lunes->value => [
                ['hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                ['hora_inicio' => '11:00', 'hora_fin' => '13:00'],
            ],
        ],
    ));

    expect($ejecutar)->toThrow(RangosDeHorarioSuperpuestosException::class);
    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(0);
});

test('un horario vacio borra todos los rangos del profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);

    HorarioTrabajo::factory()->create(['negocio_id' => $negocio->id, 'profesional_id' => $profesional->id]);

    app(GuardarHorarioSemanal::class)->ejecutar(new DatosHorarioSemanal(
        profesional: $profesional,
        rangosPorDia: [],
    ));

    expect(HorarioTrabajo::query()->where('profesional_id', $profesional->id)->count())->toBe(0);
});
