<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Negocio;
use App\Models\PagoSuscripcion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagoSuscripcion>
 */
final class PagoSuscripcionFactory extends Factory
{
    protected $model = PagoSuscripcion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'fecha' => now()->toDateString(),
            'valor' => 35000,
            'metodo' => 'transferencia',
            'periodo_desde' => now()->toDateString(),
            'periodo_hasta' => now()->addMonth()->toDateString(),
        ];
    }
}
