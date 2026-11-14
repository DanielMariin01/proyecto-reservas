<?php

declare(strict_types=1);

use App\Actions\CambiarEstadoPedido;
use App\Enums\EstadoPedidoValor;
use App\Events\PedidoCambioDeEstado;
use App\Exceptions\TransicionDeEstadoPedidoInvalidaException;
use App\Models\Negocio;
use App\Models\Pedido;
use Illuminate\Support\Facades\Event;

test('un pedido pendiente puede pasar a pagado y luego a entregado', function (): void {
    $negocio = Negocio::factory()->create();
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Pendiente]);

    $pedido = app(CambiarEstadoPedido::class)->ejecutar($pedido, EstadoPedidoValor::Pagado);
    expect($pedido->estado)->toBe(EstadoPedidoValor::Pagado);

    $pedido = app(CambiarEstadoPedido::class)->ejecutar($pedido, EstadoPedidoValor::Entregado);
    expect($pedido->estado)->toBe(EstadoPedidoValor::Entregado);
});

test('un pedido pendiente puede cancelarse', function (): void {
    $negocio = Negocio::factory()->create();
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Pendiente]);

    $pedido = app(CambiarEstadoPedido::class)->ejecutar($pedido, EstadoPedidoValor::Cancelado);

    expect($pedido->estado)->toBe(EstadoPedidoValor::Cancelado);
});

test('no se puede pasar de entregado a cualquier otro estado', function (): void {
    $negocio = Negocio::factory()->create();
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Entregado]);

    $ejecutar = fn () => app(CambiarEstadoPedido::class)->ejecutar($pedido, EstadoPedidoValor::Pagado);

    expect($ejecutar)->toThrow(TransicionDeEstadoPedidoInvalidaException::class);
});

test('dispara el evento PedidoCambioDeEstado', function (): void {
    Event::fake();
    $negocio = Negocio::factory()->create();
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Pendiente]);

    app(CambiarEstadoPedido::class)->ejecutar($pedido, EstadoPedidoValor::Pagado);

    Event::assertDispatched(PedidoCambioDeEstado::class);
});
