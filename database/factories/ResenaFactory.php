<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoResenaValor;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Resena;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resena>
 */
final class ResenaFactory extends Factory
{
    protected $model = Resena::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'cita_id' => Cita::factory(),
            'cliente_id' => Cliente::factory(),
            'profesional_id' => Profesional::factory(),
            'calificacion' => fake()->numberBetween(1, 5),
            'comentario' => fake()->sentence(12),
            'estado' => EstadoResenaValor::Publicada,
        ];
    }

    public function oculta(): static
    {
        return $this->state(fn (array $atributos): array => ['estado' => EstadoResenaValor::Oculta]);
    }
}
