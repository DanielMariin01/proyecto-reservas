<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TipoBloque;
use App\Models\Negocio;
use App\Models\Pagina;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pagina>
 */
final class PaginaFactory extends Factory
{
    protected $model = Pagina::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'bloques' => [
                ['type' => TipoBloque::Portada->value, 'data' => ['titulo' => 'Bienvenido', 'subtitulo' => '', 'imagen' => null]],
            ],
            'bloques_publicados' => null,
            'publicada_en' => null,
        ];
    }

    public function publicada(): static
    {
        return $this->state(fn (array $atributos): array => [
            'bloques_publicados' => $atributos['bloques'],
            'publicada_en' => now(),
        ]);
    }
}
