<?php

declare(strict_types=1);

use App\DatosTransferencia\DatosPopularidadServicio;
use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Servicio;
use App\Services\ServicioPopularidadServicios;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

function servicioPopularidad(): ServicioPopularidadServicios
{
    return app(ServicioPopularidadServicios::class);
}

test('ordena los servicios por citas completadas, de mas a menos', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $masPedido = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $menosPedido = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->count(3)->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $masPedido->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $menosPedido->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);

    $ranking = servicioPopularidad()->topServicios($negocio, 10);

    expect($ranking)->toHaveCount(2);
    expect($ranking->first())->toEqual(new DatosPopularidadServicio(servicioId: $masPedido->id, puesto: 1, totalReservas: 3, clientesDistintos: 3));
    expect($ranking->last()->servicioId)->toBe($menosPedido->id);
    expect($ranking->last()->puesto)->toBe(2);
});

test('no mezcla citas de otro negocio', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $otroNegocio = Negocio::factory()->create();
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $servicioOtro = Servicio::factory()->create(['negocio_id' => $otroNegocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $servicio->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);
    Cita::factory()->count(5)->create([
        'negocio_id' => $otroNegocio->id,
        'servicio_id' => $servicioOtro->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);

    $ranking = servicioPopularidad()->topServicios($negocio, 10);

    expect($ranking)->toHaveCount(1);
    expect($ranking->first()->servicioId)->toBe($servicio->id);
});

test('solo cuenta citas completada, no pendientes ni canceladas', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $servicio->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);
    Cita::factory()->pendiente()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $servicio->id,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);
    Cita::factory()->cancelada()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $servicio->id,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);

    $ranking = servicioPopularidad()->topServicios($negocio, 10);

    expect($ranking->first()->totalReservas)->toBe(1);
});

test('dentro de la ventana de 90 dias no incluye servicios sin citas recientes', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $reciente = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $viejo = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $reciente->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(10),
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $viejo->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(200),
    ]);

    $ranking = servicioPopularidad()->topServicios($negocio, 10);

    expect($ranking)->toHaveCount(1);
    expect($ranking->first()->servicioId)->toBe($reciente->id);
});

test('sin citas completadas en los ultimos 90 dias, cae al historico completo', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $servicio->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(200),
    ]);

    $ranking = servicioPopularidad()->topServicios($negocio, 10);

    expect($ranking)->toHaveCount(1);
    expect($ranking->first()->servicioId)->toBe($servicio->id);
});

test('respeta el limite pedido', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $servicios = Servicio::factory()->count(5)->create(['negocio_id' => $negocio->id]);

    foreach ($servicios as $servicio) {
        Cita::factory()->create([
            'negocio_id' => $negocio->id,
            'servicio_id' => $servicio->id,
            'estado' => EstadoCitaValor::Completada,
            'inicio' => CarbonImmutable::now()->subDays(5),
        ]);
    }

    $ranking = servicioPopularidad()->topServicios($negocio, 2);

    expect($ranking)->toHaveCount(2);
});

test('cuenta clientes distintos, no citas repetidas del mismo cliente', function (): void {
    CarbonImmutable::setTestNow('2026-11-14 12:00:00');
    $negocio = Negocio::factory()->create();
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create();

    Cita::factory()->count(3)->create([
        'negocio_id' => $negocio->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Completada,
        'inicio' => CarbonImmutable::now()->subDays(5),
    ]);

    $ranking = servicioPopularidad()->topServicios($negocio, 10);

    expect($ranking->first()->totalReservas)->toBe(3);
    expect($ranking->first()->clientesDistintos)->toBe(1);
});
