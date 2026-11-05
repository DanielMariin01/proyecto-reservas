<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MetodoPago;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
final class PagoFactory extends Factory
{
    protected $model = Pago::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'cita_id' => Cita::factory(),
            'metodo' => MetodoPago::Efectivo->value,
            'valor' => 20000,
            'propina' => 0,
        ];
    }

    public function anulado(): static
    {
        return $this->state(fn (array $atributos): array => [
            'anulado_en' => now(),
            'motivo_anulacion' => 'Registrado por error',
        ]);
    }
}
