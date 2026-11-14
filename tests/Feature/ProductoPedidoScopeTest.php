<?php

declare(strict_types=1);

use App\Models\ItemPedido;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Negocios\ContextoNegocioActual;

test('el scope global oculta productos de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    Producto::factory()->create(['negocio_id' => $negocioA->id]);
    Producto::factory()->create(['negocio_id' => $negocioB->id]);

    expect(Producto::count())->toBe(2);

    app(ContextoNegocioActual::class)->fijar($negocioA);

    expect(Producto::count())->toBe(1);
    expect(Producto::first()->negocio_id)->toBe($negocioA->id);
});

test('el scope global oculta pedidos e items de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $pedidoA = Pedido::factory()->create(['negocio_id' => $negocioA->id]);
    Pedido::factory()->create(['negocio_id' => $negocioB->id]);
    ItemPedido::factory()->create(['negocio_id' => $negocioA->id, 'pedido_id' => $pedidoA->id]);
    ItemPedido::factory()->create(['negocio_id' => $negocioB->id]);

    app(ContextoNegocioActual::class)->fijar($negocioA);

    expect(Pedido::count())->toBe(1);
    expect(ItemPedido::count())->toBe(1);
    expect(Pedido::first()->negocio_id)->toBe($negocioA->id);
    expect(ItemPedido::first()->negocio_id)->toBe($negocioA->id);
});

test('un item de pedido guarda una copia del nombre y precio del producto, no una referencia viva', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Cera para barba', 'precio' => 20000]);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id]);
    $item = ItemPedido::factory()->create([
        'negocio_id' => $negocio->id,
        'pedido_id' => $pedido->id,
        'producto_id' => $producto->id,
        'nombre_producto' => $producto->nombre,
        'precio_unitario' => $producto->precio,
        'cantidad' => 2,
        'subtotal' => $producto->precio * 2,
    ]);

    $producto->update(['nombre' => 'Cera premium', 'precio' => 50000]);
    $item->refresh();

    expect($item->nombre_producto)->toBe('Cera para barba');
    expect($item->precio_unitario)->toBe(20000);
    expect($item->subtotal)->toBe(40000);
});

test('un item de pedido sigue existiendo si el producto se borra', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id]);
    $item = ItemPedido::factory()->create([
        'negocio_id' => $negocio->id,
        'pedido_id' => $pedido->id,
        'producto_id' => $producto->id,
    ]);

    $producto->delete();
    $item->refresh();

    expect($item->producto_id)->toBeNull();
});

test('borrar un pedido borra sus items', function (): void {
    $negocio = Negocio::factory()->create();
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id]);
    $item = ItemPedido::factory()->create(['negocio_id' => $negocio->id, 'pedido_id' => $pedido->id]);

    $pedido->delete();

    expect(ItemPedido::query()->whereKey($item->id)->exists())->toBeFalse();
});

test('un pago puede quedar ligado a un pedido en vez de a una cita', function (): void {
    $negocio = Negocio::factory()->create();
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id]);
    $pago = Pago::factory()->paraPedido()->create(['negocio_id' => $negocio->id, 'pedido_id' => $pedido->id]);

    expect($pago->cita_id)->toBeNull();
    expect($pago->pedido_id)->toBe($pedido->id);
    expect($pago->pedido->id)->toBe($pedido->id);
});
