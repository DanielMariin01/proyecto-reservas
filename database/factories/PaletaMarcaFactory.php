<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaletaMarca;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaletaMarca>
 */
final class PaletaMarcaFactory extends Factory
{
    protected $model = PaletaMarca::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            // Azul oscuro por defecto: cumple AA de sobra (ver CalculadoraContraste).
            'color_primario' => '#1B263B',
            'color_secundario' => '#415A77',
        ];
    }
}
