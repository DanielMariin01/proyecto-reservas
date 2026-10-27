<?php

declare(strict_types=1);

use App\Actions\RegistrarPagoSuscripcion;
use App\DatosTransferencia\DatosPagoSuscripcion;
use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;
use App\Services\ServicioSuscripcion;

test('un negocio activo pasa a vencido cuando se cumple la fecha de pago', function (): void {
    $negocio = Negocio::factory()->create([
        'estado_suscripcion' => EstadoSuscripcion::Activo,
        'proxima_fecha_pago' => now()->subDay()->toDateString(),
    ]);

    app(ServicioSuscripcion::class)->actualizarEstados();

    expect($negocio->refresh()->estado_suscripcion)->toBe(EstadoSuscripcion::Vencido);
});

test('un negocio vencido pasa a suspendido cuando se cumple la gracia', function (): void {
    $negocio = Negocio::factory()->create([
        'estado_suscripcion' => EstadoSuscripcion::Vencido,
        'dias_gracia' => 5,
        'vencido_desde' => now()->subDays(6),
    ]);

    app(ServicioSuscripcion::class)->actualizarEstados();

    expect($negocio->refresh()->estado_suscripcion)->toBe(EstadoSuscripcion::Suspendido);
});

test('un negocio vencido dentro de la gracia no cambia de estado', function (): void {
    $negocio = Negocio::factory()->create([
        'estado_suscripcion' => EstadoSuscripcion::Vencido,
        'dias_gracia' => 5,
        'vencido_desde' => now()->subDays(2),
    ]);

    app(ServicioSuscripcion::class)->actualizarEstados();

    expect($negocio->refresh()->estado_suscripcion)->toBe(EstadoSuscripcion::Vencido);
});

test('un negocio suspendido pasa a cancelado cuando se cumple el plazo', function (): void {
    $negocio = Negocio::factory()->create([
        'estado_suscripcion' => EstadoSuscripcion::Suspendido,
        'meses_para_cancelar' => 3,
        'suspendido_desde' => now()->subMonths(4),
    ]);

    app(ServicioSuscripcion::class)->actualizarEstados();

    expect($negocio->refresh()->estado_suscripcion)->toBe(EstadoSuscripcion::Cancelado);
});

test('registrar un pago reactiva un negocio suspendido', function (): void {
    $negocio = Negocio::factory()->suspendido()->create();

    app(RegistrarPagoSuscripcion::class)->ejecutar(new DatosPagoSuscripcion(
        negocio: $negocio,
        fecha: now(),
        valor: 35000,
        metodo: 'transferencia',
        periodoDesde: now(),
        periodoHasta: now()->addMonth(),
    ));

    expect($negocio->refresh()->estado_suscripcion)->toBe(EstadoSuscripcion::Activo);
});

test('un negocio cancelado no cambia automaticamente de estado', function (): void {
    $negocio = Negocio::factory()->cancelado()->create();

    app(ServicioSuscripcion::class)->actualizarEstados();

    expect($negocio->refresh()->estado_suscripcion)->toBe(EstadoSuscripcion::Cancelado);
});
