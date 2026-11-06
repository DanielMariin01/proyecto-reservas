<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CuentaCliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CuentaCliente>
 */
final class CuentaClienteFactory extends Factory
{
    protected $model = CuentaCliente::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'google_id' => Str::random(21),
        ];
    }
}
