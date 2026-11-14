<?php

declare(strict_types=1);

use App\Enums\EstadoPedidoValor;
use App\Estados\Pedido\EstadoCancelado;
use App\Estados\Pedido\EstadoEntregado;
use App\Estados\Pedido\EstadoPagado;
use App\Estados\Pedido\EstadoPendiente;
use App\Estados\Pedido\FabricaEstadoPedido;

test('pendiente puede pasar a pagado o cancelado, nada mas', function (): void {
    $estado = new EstadoPendiente;

    expect($estado->puedeTransicionarA(EstadoPedidoValor::Pagado))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoPedidoValor::Cancelado))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoPedidoValor::Entregado))->toBeFalse();
    expect($estado->esFinal())->toBeFalse();
});

test('pagado puede pasar a entregado o cancelado, nada mas', function (): void {
    $estado = new EstadoPagado;

    expect($estado->puedeTransicionarA(EstadoPedidoValor::Entregado))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoPedidoValor::Cancelado))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoPedidoValor::Pendiente))->toBeFalse();
    expect($estado->esFinal())->toBeFalse();
});

test('entregado y cancelado son finales y no aceptan ninguna transicion', function (): void {
    foreach ([new EstadoEntregado, new EstadoCancelado] as $estado) {
        expect($estado->esFinal())->toBeTrue();

        foreach (EstadoPedidoValor::cases() as $destino) {
            expect($estado->puedeTransicionarA($destino))->toBeFalse();
        }
    }
});

test('la fabrica reconstruye la clase de estado correcta para cada valor', function (): void {
    $fabrica = new FabricaEstadoPedido;

    foreach (EstadoPedidoValor::cases() as $valor) {
        expect($fabrica->paraValor($valor)->valor())->toBe($valor);
    }
});
