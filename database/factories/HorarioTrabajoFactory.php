<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DiaSemana;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HorarioTrabajo>
 */
final class HorarioTrabajoFactory extends Factory
{
    protected $model = HorarioTrabajo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'profesional_id' => Profesional::factory(),
            'dia_semana' => DiaSemana::Lunes,
            'hora_inicio' => '10:00:00',
            'hora_fin' => '12:00:00',
        ];
    }
}
