<?php

declare(strict_types=1);

use App\Actions\AnularPago;
use App\Events\PagoAnulado;
use App\Exceptions\PagoYaAnuladoException;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Support\Facades\Event;

function citaConPago(): array
{
    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
    ]);

    $pago = Pago::factory()->create([
        'negocio_id' => $negocio->id,
        'cita_id' => $cita->id,
    ]);

    return ['negocio' => $negocio, 'cita' => $cita, 'pago' => $pago];
}

test('anular un pago lo marca con motivo y fecha, sin borrarlo', function (): void {
    ['pago' => $pago] = citaConPago();

    $pago = app(AnularPago::class)->ejecutar($pago, 'Se registró por error');

    expect($pago->anulado_en)->not->toBeNull();
    expect($pago->motivo_anulacion)->toBe('Se registró por error');
    expect($pago->estaActivo())->toBeFalse();
    expect(Pago::query()->whereKey($pago->id)->exists())->toBeTrue();
});

test('no se puede anular un pago que ya estaba anulado', function (): void {
    ['pago' => $pago] = citaConPago();
    app(AnularPago::class)->ejecutar($pago, 'Primer motivo');

    $ejecutar = fn () => app(AnularPago::class)->ejecutar($pago, 'Segundo motivo');

    expect($ejecutar)->toThrow(PagoYaAnuladoException::class);
});

test('dispara el evento PagoAnulado', function (): void {
    Event::fake();
    ['pago' => $pago] = citaConPago();

    app(AnularPago::class)->ejecutar($pago, 'Motivo');

    Event::assertDispatched(PagoAnulado::class);
});

test('un pago anulado ya no es el pago activo de la cita', function (): void {
    ['cita' => $cita, 'pago' => $pago] = citaConPago();

    app(AnularPago::class)->ejecutar($pago, 'Motivo');

    expect($cita->pagoActivo())->toBeNull();
});
