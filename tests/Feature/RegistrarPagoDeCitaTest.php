<?php

declare(strict_types=1);

use App\Actions\RegistrarPagoDeCita;
use App\Enums\EstadoCitaValor;
use App\Enums\MetodoPago;
use App\Events\PagoRegistrado;
use App\Exceptions\CitaNoCompletadaException;
use App\Exceptions\MontoDePagoIncorrectoException;
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

test('no se puede registrar un pago por un monto distinto al precio de la cita', function (): void {
    $cita = citaCompletada(['precio' => 25000]);

    $ejecutar = fn () => app(RegistrarPagoDeCita::class)->ejecutar($cita, MetodoPago::Efectivo, 20000);

    expect($ejecutar)->toThrow(MontoDePagoIncorrectoException::class);
    expect($cita->pagoActivo())->toBeNull();
});

test('la propina no afecta la validacion del monto: solo el valor debe coincidir con el precio', function (): void {
    $cita = citaCompletada(['precio' => 25000]);

    $pago = app(RegistrarPagoDeCita::class)->ejecutar($cita, MetodoPago::Efectivo, 25000, propina: 50000);

    expect($pago->valor)->toBe(25000);
    expect($pago->propina)->toBe(50000);
});

test('validar() no toca nada, solo lanza si el monto no coincide', function (): void {
    $cita = citaCompletada(['precio' => 25000]);

    app(RegistrarPagoDeCita::class)->validar($cita, 25000);

    $validarConMontoIncorrecto = fn () => app(RegistrarPagoDeCita::class)->validar($cita, 10000);

    expect($validarConMontoIncorrecto)->toThrow(MontoDePagoIncorrectoException::class);
    expect($cita->pagoActivo())->toBeNull();
});

test('dispara el evento PagoRegistrado', function (): void {
    Event::fake();
    $cita = citaCompletada();

    app(RegistrarPagoDeCita::class)->ejecutar($cita, MetodoPago::Efectivo, 25000);

    Event::assertDispatched(PagoRegistrado::class);
});
