<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CuentaCliente;
use App\Models\DisenoFavorito;
use App\Models\Negocio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DisenoFavorito>
 */
final class DisenoFavoritoFactory extends Factory
{
    protected $model = DisenoFavorito::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cuenta_cliente_id' => CuentaCliente::factory(),
            'negocio_id' => Negocio::factory(),
            'diseno_id' => (string) Str::ulid(),
        ];
    }
}
