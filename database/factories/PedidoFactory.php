<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoPedidoValor;
use App\Enums\TipoEntregaValor;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
final class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'cliente_id' => Cliente::factory(),
            'estado' => EstadoPedidoValor::Pendiente,
            'tipo_entrega' => TipoEntregaValor::Recoger,
            'barrio' => null,
            'total' => 25000,
        ];
    }

    public function domicilio(): static
    {
        return $this->state(fn (array $atributos): array => [
            'tipo_entrega' => TipoEntregaValor::Domicilio,
            'barrio' => fake()->streetName(),
        ]);
    }

    public function pagado(): static
    {
        return $this->state(fn (array $atributos): array => ['estado' => EstadoPedidoValor::Pagado]);
    }

    public function entregado(): static
    {
        return $this->state(fn (array $atributos): array => ['estado' => EstadoPedidoValor::Entregado]);
    }

    public function cancelado(): static
    {
        return $this->state(fn (array $atributos): array => ['estado' => EstadoPedidoValor::Cancelado]);
    }
}
