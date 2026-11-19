<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Gasto;
use App\Models\Negocio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gasto>
 */
final class GastoFactory extends Factory
{
    protected $model = Gasto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'monto' => 20000,
            'descripcion' => $this->faker->sentence(3),
            'fecha' => now()->toDateString(),
        ];
    }
}
