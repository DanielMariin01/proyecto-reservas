<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\CategoriaServicio;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;

test('un dueno no ve categorias de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    CategoriaServicio::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Categoria de B']);

    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $respuesta = $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/categorias-servicio");

    $respuesta->assertOk();
    $respuesta->assertDontSee('Categoria de B');
});

test('un dueno no ve servicios de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    Servicio::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Servicio de B']);

    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $respuesta = $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/servicios");

    $respuesta->assertOk();
    $respuesta->assertDontSee('Servicio de B');
});

test('un dueno no ve profesionales de otro negocio', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    Profesional::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Profesional de B']);

    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $respuesta = $this->actingAs($duenoA)->get("/app/{$negocioA->slug}/profesionales");

    $respuesta->assertOk();
    $respuesta->assertDontSee('Profesional de B');
});
