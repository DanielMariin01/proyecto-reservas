<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Negocio;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servicio>
 */
final class ServicioFactory extends Factory
{
    protected $model = Servicio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'nombre' => fake()->randomElement(['Corte clásico', 'Corte + Barba', 'Manicura spa', 'Pedicura spa']),
            'descripcion' => fake()->sentence(),
            'duracion_minutos' => fake()->randomElement([30, 45, 60]),
            'precio' => fake()->randomElement([20000, 25000, 35000, 50000]),
            'visible' => true,
            'orden' => 0,
        ];
    }

    public function oculto(): static
    {
        return $this->state(fn (array $atributos): array => [
            'visible' => false,
        ]);
    }
}
