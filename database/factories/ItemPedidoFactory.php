<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ItemPedido;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemPedido>
 */
final class ItemPedidoFactory extends Factory
{
    protected $model = ItemPedido::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $precioUnitario = fake()->randomElement([15000, 25000, 35000]);
        $cantidad = fake()->numberBetween(1, 3);

        return [
            'negocio_id' => Negocio::factory(),
            'pedido_id' => Pedido::factory(),
            'producto_id' => Producto::factory(),
            'nombre_producto' => fake()->randomElement(['Cera para barba', 'Shampoo anticaspa', 'Aceite para uñas']),
            'precio_unitario' => $precioUnitario,
            'cantidad' => $cantidad,
            'subtotal' => $precioUnitario * $cantidad,
        ];
    }
}
