<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TipoBloqueo;
use App\Models\Bloqueo;
use App\Models\Negocio;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bloqueo>
 */
final class BloqueoFactory extends Factory
{
    protected $model = Bloqueo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'profesional_id' => Profesional::factory(),
            'tipo' => TipoBloqueo::Bloqueo,
            'inicio' => now(),
            'fin' => now()->addHour(),
            'motivo' => 'Prueba',
        ];
    }

    public function apertura(): static
    {
        return $this->state(fn (array $atributos): array => [
            'tipo' => TipoBloqueo::Apertura,
        ]);
    }
}
