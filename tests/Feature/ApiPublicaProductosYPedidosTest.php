<?php

declare(strict_types=1);

use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Producto;

test('GET productos solo devuelve los visibles', function (): void {
    $negocio = Negocio::factory()->create();
    $productoVisible = Producto::factory()->create(['negocio_id' => $negocio->id, 'visible' => true]);
    $productoOculto = Producto::factory()->create(['negocio_id' => $negocio->id, 'visible' => false]);

    $respuesta = $this->getJson("/api/publico/{$negocio->slug}/productos");

    $respuesta->assertOk();
    $ids = collect($respuesta->json('data'))->pluck('id');
    expect($ids)->toContain($productoVisible->id);
    expect($ids)->not->toContain($productoOculto->id);
});

test('POST pedidos crea el pedido con sus items y el cliente', function (): void {
    $negocio = Negocio::factory()->create();
    $productoA = Producto::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Cera', 'precio' => 15000]);
    $productoB = Producto::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Shampoo', 'precio' => 25000]);

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/pedidos", [
        'items' => [
            ['producto_id' => $productoA->id, 'cantidad' => 2],
            ['producto_id' => $productoB->id, 'cantidad' => 1],
        ],
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'tipo_entrega' => 'recoger',
        'autorizacion_datos' => true,
    ]);

    $respuesta->assertCreated();
    expect($respuesta->json('data.total'))->toBe(55000);
    expect($respuesta->json('data.items'))->toHaveCount(2);
    $this->assertDatabaseHas('pedidos', ['negocio_id' => $negocio->id, 'total' => 55000]);
});

test('POST pedidos con domicilio exige el barrio', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/pedidos", [
        'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'tipo_entrega' => 'domicilio',
        'autorizacion_datos' => true,
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['barrio']);
});

test('POST pedidos exige autorizar el tratamiento de datos', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/pedidos", [
        'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'tipo_entrega' => 'recoger',
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['autorizacion_datos']);
});

test('POST pedidos rechaza el envio si el campo trampa viene lleno', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/pedidos", [
        'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'tipo_entrega' => 'recoger',
        'autorizacion_datos' => true,
        'sitio_web' => 'http://spam.com',
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['sitio_web']);
});

test('POST pedidos no acepta un producto de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $productoB = Producto::factory()->create(['negocio_id' => $negocioB->id]);

    $respuesta = $this->postJson("/api/publico/{$negocioA->slug}/pedidos", [
        'items' => [['producto_id' => $productoB->id, 'cantidad' => 1]],
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'tipo_entrega' => 'recoger',
        'autorizacion_datos' => true,
    ]);

    $respuesta->assertUnprocessable()->assertJsonValidationErrors(['items.0.producto_id']);
    expect(Pedido::query()->count())->toBe(0);
});

test('un negocio suspendido no expone productos ni acepta pedidos', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
    $negocio->update(['estado_suscripcion' => EstadoSuscripcion::Suspendido]);

    $respuestaProductos = $this->getJson("/api/publico/{$negocio->slug}/productos");
    $respuestaPedido = $this->postJson("/api/publico/{$negocio->slug}/pedidos", [
        'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'tipo_entrega' => 'recoger',
        'autorizacion_datos' => true,
    ]);

    $respuestaProductos->assertStatus(404);
    $respuestaPedido->assertStatus(404);
});

test('el endpoint de pedidos tiene un limite de intentos estricto', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $datos = [
        'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
        'nombre' => 'Ana',
        'celular' => '3001234567',
        'tipo_entrega' => 'recoger',
        'autorizacion_datos' => true,
    ];

    for ($intento = 1; $intento <= 5; $intento++) {
        $this->postJson("/api/publico/{$negocio->slug}/pedidos", $datos);
    }

    $respuesta = $this->postJson("/api/publico/{$negocio->slug}/pedidos", $datos);

    $respuesta->assertStatus(429);
});
