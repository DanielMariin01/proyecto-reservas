<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoSuscripcion;
use App\Enums\TipoNegocio;
use App\Models\Negocio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Negocio>
 */
final class NegocioFactory extends Factory
{
    protected $model = Negocio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->company();

        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre).'-'.fake()->unique()->numberBetween(1, 99999),
            'tipo' => fake()->randomElement(TipoNegocio::cases()),
            'direccion' => fake()->streetAddress(),
            'ciudad' => fake()->city(),
            'telefono' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'zona_horaria' => 'America/Bogota',
            'estado_suscripcion' => EstadoSuscripcion::Activo,
            'proxima_fecha_pago' => now()->addMonth()->toDateString(),
        ];
    }

    public function vencido(): static
    {
        return $this->state(fn (array $atributos): array => [
            'estado_suscripcion' => EstadoSuscripcion::Vencido,
            'proxima_fecha_pago' => now()->subDay()->toDateString(),
            'vencido_desde' => now()->subDay(),
        ]);
    }

    public function suspendido(): static
    {
        return $this->state(fn (array $atributos): array => [
            'estado_suscripcion' => EstadoSuscripcion::Suspendido,
            'suspendido_desde' => now()->subDay(),
        ]);
    }

    public function cancelado(): static
    {
        return $this->state(fn (array $atributos): array => [
            'estado_suscripcion' => EstadoSuscripcion::Cancelado,
            'cancelado_desde' => now()->subDay(),
        ]);
    }
}
