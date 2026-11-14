<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;

test('el dueno puede gestionar productos y pedidos de su negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id]);

    expect($dueno->can('viewAny', Producto::class))->toBeTrue();
    expect($dueno->can('create', Producto::class))->toBeTrue();
    expect($dueno->can('update', $producto))->toBeTrue();
    expect($dueno->can('delete', $producto))->toBeTrue();
    expect($dueno->can('viewAny', Pedido::class))->toBeTrue();
    expect($dueno->can('update', $pedido))->toBeTrue();
});

test('la recepcion sin el permiso de gestionar cursos y productos no puede gestionarlos', function (): void {
    $negocio = Negocio::factory()->create();
    $recepcion = crearUsuarioConRol($negocio, RolNegocio::Recepcion);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id]);

    expect($recepcion->can('viewAny', Producto::class))->toBeFalse();
    expect($recepcion->can('create', Producto::class))->toBeFalse();
    expect($recepcion->can('update', $producto))->toBeFalse();
    expect($recepcion->can('viewAny', Pedido::class))->toBeFalse();
    expect($recepcion->can('update', $pedido))->toBeFalse();
});

test('el profesional no puede gestionar productos ni pedidos', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);

    expect($profesional->can('viewAny', Producto::class))->toBeFalse();
    expect($profesional->can('update', $producto))->toBeFalse();
});

test('el super admin puede gestionar productos y pedidos de cualquier negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $superAdmin = Usuario::factory()->create(['es_super_admin' => true]);
    $producto = Producto::factory()->create(['negocio_id' => $negocio->id]);
    $pedido = Pedido::factory()->create(['negocio_id' => $negocio->id]);

    expect($superAdmin->can('update', $producto))->toBeTrue();
    expect($superAdmin->can('update', $pedido))->toBeTrue();
});
