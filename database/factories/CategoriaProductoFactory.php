<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoriaProducto;
use App\Models\Negocio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoriaProducto>
 */
final class CategoriaProductoFactory extends Factory
{
    protected $model = CategoriaProducto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'nombre' => fake()->randomElement(['Cuidado capilar', 'Cuidado de barba', 'Cuidado de uñas', 'Aseo personal']),
            'orden' => 0,
        ];
    }
}
