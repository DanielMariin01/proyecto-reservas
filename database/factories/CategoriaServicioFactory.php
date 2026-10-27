<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoriaServicio;
use App\Models\Negocio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoriaServicio>
 */
final class CategoriaServicioFactory extends Factory
{
    protected $model = CategoriaServicio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'nombre' => fake()->randomElement(['Cortes', 'Barba', 'Manicura', 'Pedicura', 'Adicionales']),
            'orden' => 0,
        ];
    }
}
