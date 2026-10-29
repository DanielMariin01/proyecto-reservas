<?php

declare(strict_types=1);

use App\Actions\RegistrarPagoDeCita;
use App\Enums\EstadoCitaValor;
use App\Enums\MetodoPago;
use App\Events\PagoRegistrado;
use App\Exceptions\CitaNoCompletadaException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Support\Facades\Event;

function citaCompletada(array $atributos = []): Cita
{
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    return Cita::factory()->create(array_merge([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Completada,
        'precio' => 25000,
    ], $atributos));
}

test('registra el pago de una cita completada', function (): void {
    $cita = citaCompletada();

    $pago = app(RegistrarPagoDeCita::class)->ejecutar($cita, MetodoPago::Nequi, 25000, 3000);

    expect($pago->metodo)->toBe(MetodoPago::Nequi);
    expect($pago->valor)->toBe(25000);
    expect($pago->propina)->toBe(3000);
    expect($pago->negocio_id)->toBe($cita->negocio_id);
    expect($pago->estaActivo())->toBeTrue();
});

test('no se puede registrar un pago si la cita no esta completada', function (): void {
    $cita = citaCompletada(['estado' => EstadoCitaValor::Confirmada]);

    $ejecutar = fn () => app(RegistrarPagoDeCita::class)->ejecutar($cita, MetodoPago::Efectivo, 25000);

    expect($ejecutar)->toThrow(CitaNoCompletadaException::class);
});

test('dispara el evento PagoRegistrado', function (): void {
    Event::fake();
    $cita = citaCompletada();

    app(RegistrarPagoDeCita::class)->ejecutar($cita, MetodoPago::Efectivo, 25000);

    Event::assertDispatched(PagoRegistrado::class);
});
