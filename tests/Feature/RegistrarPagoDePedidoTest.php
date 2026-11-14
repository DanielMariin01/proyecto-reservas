<?php

declare(strict_types=1);

use App\Actions\RegistrarPagoDePedido;
use App\Enums\EstadoPedidoValor;
use App\Enums\MetodoPago;
use App\Exceptions\PedidoNoPagadoException;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Usuario;

test('registra el pago de un pedido ya marcado como pagado', function (): void {
    $negocio = Negocio::factory()->create();
    $usuario = Usuario::factory()->create();
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Pagado, 'total' => 30000]);

    $pago = app(RegistrarPagoDePedido::class)->ejecutar($pedido, MetodoPago::Efectivo, 30000, $usuario);

    expect($pago->pedido_id)->toBe($pedido->id);
    expect($pago->cita_id)->toBeNull();
    expect($pago->valor)->toBe(30000);
    expect($pago->registrado_por_id)->toBe($usuario->id);
});

test('no se puede registrar el pago de un pedido que no esta marcado como pagado', function (): void {
    $negocio = Negocio::factory()->create();
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Pendiente]);

    $ejecutar = fn () => app(RegistrarPagoDePedido::class)->ejecutar($pedido, MetodoPago::Efectivo, 20000);

    expect($ejecutar)->toThrow(PedidoNoPagadoException::class);
});
