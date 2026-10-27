<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Negocio;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profesional>
 */
final class ProfesionalFactory extends Factory
{
    protected $model = Profesional::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'nombre' => fake()->name(),
            'especialidad' => fake()->randomElement(['Barbero', 'Manicurista', 'Estilista']),
            'color' => fake()->hexColor(),
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $atributos): array => [
            'activo' => false,
        ]);
    }
}
