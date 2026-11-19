<?php

declare(strict_types=1);

use App\Actions\CrearPedido;
use App\DatosTransferencia\DatosItemPedido;
use App\DatosTransferencia\DatosPedido;
use App\Enums\EstadoPedidoValor;
use App\Enums\TipoEntregaValor;
use App\Events\PedidoCreado;
use App\Exceptions\CarritoVacioException;
use App\Exceptions\EntregaInvalidaException;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Event;

test('crea un cliente nuevo cuando el celular no existe en el negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $pedido = app(CrearPedido::class)->ejecutar(new DatosPedido(
        negocio: $negocio,
        items: [new DatosItemPedido(producto: $producto, cantidad: 1)],
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        tipoEntrega: TipoEntregaValor::Recoger,
    ));

    expect(Cliente::query()->where('negocio_id', $negocio->id)->where('celular', '3001234567')->count())->toBe(1);
    expect($pedido->cliente_id)->toBe(Cliente::query()->where('celular', '3001234567')->first()->id);
});

test('reutiliza el cliente existente con el mismo celular en vez de duplicarlo', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
    $clienteExistente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'celular' => '3001234567']);

    $pedido = app(CrearPedido::class)->ejecutar(new DatosPedido(
        negocio: $negocio,
        items: [new DatosItemPedido(producto: $producto, cantidad: 1)],
        nombreCliente: 'Otro nombre',
        celularCliente: '3001234567',
        correoCliente: null,
        tipoEntrega: TipoEntregaValor::Recoger,
    ));

    expect(Cliente::query()->where('negocio_id', $negocio->id)->where('celular', '3001234567')->count())->toBe(1);
    expect($pedido->cliente_id)->toBe($clienteExistente->id);
});

test('calcula el total y el subtotal de cada item a partir de los productos', function (): void {
    $negocio = Negocio::factory()->create();
    $productoA = Producto::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Cera', 'precio' => 15000]);
    $productoB = Producto::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Shampoo', 'precio' => 25000]);

    $pedido = app(CrearPedido::class)->ejecutar(new DatosPedido(
        negocio: $negocio,
        items: [
            new DatosItemPedido(producto: $productoA, cantidad: 2),
            new DatosItemPedido(producto: $productoB, cantidad: 1),
        ],
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        tipoEntrega: TipoEntregaValor::Recoger,
    ));

    expect($pedido->total)->toBe(55000); // (15000*2) + (25000*1)
    expect($pedido->items)->toHaveCount(2);

    $itemA = $pedido->items->firstWhere('producto_id', $productoA->id);
    expect($itemA->nombre_producto)->toBe('Cera');
    expect($itemA->precio_unitario)->toBe(15000);
    expect($itemA->cantidad)->toBe(2);
    expect($itemA->subtotal)->toBe(30000);
});

test('el pedido queda pendiente al crearse', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $pedido = app(CrearPedido::class)->ejecutar(new DatosPedido(
        negocio: $negocio,
        items: [new DatosItemPedido(producto: $producto, cantidad: 1)],
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        tipoEntrega: TipoEntregaValor::Recoger,
    ));

    expect($pedido->estado)->toBe(EstadoPedidoValor::Pendiente);
});

test('domicilio sin barrio lanza una excepcion y no crea nada', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $ejecutar = fn () => app(CrearPedido::class)->ejecutar(new DatosPedido(
        negocio: $negocio,
        items: [new DatosItemPedido(producto: $producto, cantidad: 1)],
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        tipoEntrega: TipoEntregaValor::Domicilio,
        barrio: null,
    ));

    expect($ejecutar)->toThrow(EntregaInvalidaException::class);
    expect(Pedido::query()->count())->toBe(0);
});

test('domicilio con barrio no lanza ninguna excepcion', function (): void {
    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    $pedido = app(CrearPedido::class)->ejecutar(new DatosPedido(
        negocio: $negocio,
        items: [new DatosItemPedido(producto: $producto, cantidad: 1)],
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        tipoEntrega: TipoEntregaValor::Domicilio,
        barrio: 'El Porvenir',
    ));

    expect($pedido->barrio)->toBe('El Porvenir');
});

test('un carrito vacio lanza una excepcion', function (): void {
    $negocio = Negocio::factory()->create();

    $ejecutar = fn () => app(CrearPedido::class)->ejecutar(new DatosPedido(
        negocio: $negocio,
        items: [],
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        tipoEntrega: TipoEntregaValor::Recoger,
    ));

    expect($ejecutar)->toThrow(CarritoVacioException::class);
});

test('dispara el evento PedidoCreado al crear el pedido', function (): void {
    Event::fake();

    $negocio = Negocio::factory()->create();
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    app(CrearPedido::class)->ejecutar(new DatosPedido(
        negocio: $negocio,
        items: [new DatosItemPedido(producto: $producto, cantidad: 1)],
        nombreCliente: 'Ana',
        celularCliente: '3001234567',
        correoCliente: null,
        tipoEntrega: TipoEntregaValor::Recoger,
    ));

    Event::assertDispatched(PedidoCreado::class);
});
