<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
final class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'nombre' => fake()->randomElement(['Cera para barba', 'Shampoo anticaspa', 'Aceite para uñas', 'Crema hidratante']),
            'descripcion' => fake()->sentence(),
            'precio' => fake()->randomElement([15000, 25000, 35000, 45000]),
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
