<?php

declare(strict_types=1);

use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\CuentaCliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;

test('las estadisticas cuentan las citas propias agrupadas por estado, sin mezclar otras cuentas', function (): void {
    $cuenta = CuentaCliente::factory()->create();
    $otraCuenta = CuentaCliente::factory()->create();

    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'cuenta_cliente_id' => $cuenta->id]);
    $clienteAjeno = Cliente::factory()->create(['negocio_id' => $negocio->id, 'cuenta_cliente_id' => $otraCuenta->id]);

    foreach ([EstadoCitaValor::Pendiente, EstadoCitaValor::Confirmada, EstadoCitaValor::Completada, EstadoCitaValor::Completada, EstadoCitaValor::Cancelada] as $estado) {
        Cita::factory()->create([
            'negocio_id' => $negocio->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'cliente_id' => $cliente->id,
            'estado' => $estado,
        ]);
    }

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $clienteAjeno->id,
        'estado' => EstadoCitaValor::Completada,
    ]);

    $respuesta = $this->actingAs($cuenta, 'cliente')->getJson('/mi-cuenta/api/estadisticas');

    $respuesta->assertOk();
    $respuesta->assertJson([
        'citasTotales' => 5,
        'citasPendientes' => 2,
        'citasCompletadas' => 2,
        'citasCanceladas' => 1,
        'citasNoAsistio' => 0,
    ]);
});
