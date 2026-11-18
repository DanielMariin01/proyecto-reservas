<?php

declare(strict_types=1);

use App\Enums\EstadoPedidoValor;
use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Pedido;

test('un profesional no puede ver la pantalla de pedidos ni la api', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    Pedido::factory()->create(['negocio_id' => $negocio->id]);

    $this->actingAs($profesional)->get("/app/{$negocio->slug}/pedidos")->assertForbidden();
    $this->actingAs($profesional)->getJson("/app/{$negocio->slug}/api/pedidos")->assertForbidden();
});

test('el dueno ve la pantalla de pedidos y la api lista solo los del negocio actual', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    Pedido::factory()->create(['negocio_id' => $negocioA->id]);
    Pedido::factory()->create(['negocio_id' => $negocioB->id]);

    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/pedidos")->assertOk();
    $respuesta = $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/pedidos");

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(1);
});

test('cambiar el estado de un pedido pendiente a cancelado funciona desde la ruta', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Pendiente]);

    $respuesta = $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/pedidos/{$pedido->id}/estado", [
        'estado' => 'cancelado',
    ]);

    $respuesta->assertOk();
    expect($pedido->refresh()->estado)->toBe(EstadoPedidoValor::Cancelado);
});

test('una transicion de estado invalida devuelve 422 y no cambia el pedido', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Entregado]);

    $respuesta = $this->actingAs($dueno)->putJson("/app/{$negocio->slug}/api/pedidos/{$pedido->id}/estado", [
        'estado' => 'pagado',
    ]);

    $respuesta->assertStatus(422);
    expect($pedido->refresh()->estado)->toBe(EstadoPedidoValor::Entregado);
});

test('marcar pagado cambia el estado y registra el pago', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Pendiente, 'total' => 25000]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/pedidos/{$pedido->id}/marcar-pagado", [
        'metodo' => 'efectivo',
        'valor' => 25000,
    ]);

    $respuesta->assertOk();
    expect($pedido->refresh()->estado)->toBe(EstadoPedidoValor::Pagado);
    $this->assertDatabaseHas('pagos', ['pedido_id' => $pedido->id, 'valor' => 25000]);
});

test('marcar pagado con un monto que no coincide con el total se rechaza y el pedido se queda pendiente', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id, 'estado' => EstadoPedidoValor::Pendiente, 'total' => 25000]);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/pedidos/{$pedido->id}/marcar-pagado", [
        'metodo' => 'efectivo',
        'valor' => 1000,
    ]);

    $respuesta->assertStatus(422);
    $respuesta->assertJson(['mensaje' => 'El monto pagado ($1.000) no coincide con el total a cobrar ($25.000).']);
    // El pedido se queda Pendiente, no "Pagado sin pago": Pagado solo
    // puede pasar a Entregado o Cancelado, nunca de vuelta a Pendiente.
    expect($pedido->refresh()->estado)->toBe(EstadoPedidoValor::Pendiente);
    $this->assertDatabaseMissing('pagos', ['pedido_id' => $pedido->id]);
});

test('un dueno no puede cambiar el estado de un pedido de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $pedidoB = Pedido::factory()->create(['negocio_id' => $negocioB->id, 'estado' => EstadoPedidoValor::Pendiente]);

    $this->actingAs($duenoA)->putJson("/app/{$negocioA->slug}/api/pedidos/{$pedidoB->id}/estado", [
        'estado' => 'cancelado',
    ])->assertNotFound();

    expect($pedidoB->refresh()->estado)->toBe(EstadoPedidoValor::Pendiente);
    expect(Pago::query()->count())->toBe(0);
});
