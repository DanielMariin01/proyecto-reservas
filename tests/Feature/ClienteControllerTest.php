<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Enums\TipoNegocio;
use App\Models\Cliente;
use App\Models\Negocio;

test('un profesional de un negocio que no es spa no puede ver la lista de clientes', function (): void {
    $negocio = Negocio::factory()->create(['tipo' => TipoNegocio::Barberia]);
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $this->actingAs($profesional)->get("/app/{$negocio->slug}/clientes")->assertForbidden();
});

test('el dueno ve la lista y la ficha de un cliente', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Ana Pérez']);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/clientes")->assertOk();
    $this->actingAs($dueno)->get("/app/{$negocio->slug}/clientes/{$cliente->id}")->assertOk();
});

test('la api de clientes busca por nombre o celular y aisla por negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    Cliente::factory()->create(['negocio_id' => $negocioA->id, 'nombre' => 'Ana Pérez', 'celular' => '3001234567']);
    Cliente::factory()->create(['negocio_id' => $negocioA->id, 'nombre' => 'Carlos Ruiz', 'celular' => '3009876543']);
    Cliente::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Ana Gómez', 'celular' => '3005551234']);

    $respuesta = $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/clientes?buscar=Ana");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['nombre'])->toBe('Ana Pérez');
});

test('un dueno no puede ver la ficha de un cliente de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $clienteB = Cliente::factory()->create(['negocio_id' => $negocioB->id]);

    $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/clientes/{$clienteB->id}")->assertNotFound();
    $this->actingAs($duenoA)->getJson("/app/{$negocioA->slug}/api/clientes/{$clienteB->id}")->assertNotFound();
});
