<?php

declare(strict_types=1);

use App\Models\Cliente;
use App\Models\CuentaCliente;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Profesional;
use Illuminate\Support\Facades\Storage;

test('un cliente ve su historial clinico de todos los negocios donde tiene cuenta', function (): void {
    $cuenta = CuentaCliente::factory()->create();

    $negocioA = Negocio::factory()->create();
    $clienteA = Cliente::factory()->create(['negocio_id' => $negocioA->id, 'cuenta_cliente_id' => $cuenta->id]);
    $profesionalA = Profesional::factory()->create(['negocio_id' => $negocioA->id, 'nombre' => 'Camila']);
    HistoriaClinica::factory()->create(['negocio_id' => $negocioA->id, 'cliente_id' => $clienteA->id, 'profesional_id' => $profesionalA->id]);

    $negocioB = Negocio::factory()->create();
    $clienteB = Cliente::factory()->create(['negocio_id' => $negocioB->id, 'cuenta_cliente_id' => $cuenta->id]);
    $profesionalB = Profesional::factory()->create(['negocio_id' => $negocioB->id, 'nombre' => 'Sofía']);
    HistoriaClinica::factory()->create(['negocio_id' => $negocioB->id, 'cliente_id' => $clienteB->id, 'profesional_id' => $profesionalB->id]);

    $respuesta = $this->actingAs($cuenta, 'cliente')->getJson('/mi-cuenta/api/historial-clinico');

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(2);
});

test('un cliente no ve el historial clinico de otra cuenta aunque conozca el id', function (): void {
    $cuentaPropia = CuentaCliente::factory()->create();
    $cuentaAjena = CuentaCliente::factory()->create();

    $negocio = Negocio::factory()->create();
    $clienteAjeno = Cliente::factory()->create(['negocio_id' => $negocio->id, 'cuenta_cliente_id' => $cuentaAjena->id]);
    $historiaAjena = HistoriaClinica::factory()->create(['negocio_id' => $negocio->id, 'cliente_id' => $clienteAjeno->id]);

    $respuesta = $this->actingAs($cuentaPropia, 'cliente')->getJson('/mi-cuenta/api/historial-clinico');
    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(0);

    $this->actingAs($cuentaPropia, 'cliente')
        ->getJson("/mi-cuenta/api/historial-clinico/{$historiaAjena->id}/firma-clienta")
        ->assertForbidden();
});

test('la firma del historial propio se sirve desde el disco privado', function (): void {
    Storage::fake('local');
    $cuenta = CuentaCliente::factory()->create();
    $negocio = Negocio::factory()->create();
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'cuenta_cliente_id' => $cuenta->id]);
    Storage::disk('local')->put('historias-clinicas/firma.png', 'contenido-de-prueba');
    $historia = HistoriaClinica::factory()->create([
        'negocio_id' => $negocio->id,
        'cliente_id' => $cliente->id,
        'firma_clienta_path' => 'historias-clinicas/firma.png',
    ]);

    $respuesta = $this->actingAs($cuenta, 'cliente')->get("/mi-cuenta/api/historial-clinico/{$historia->id}/firma-clienta");

    $respuesta->assertOk();
});
