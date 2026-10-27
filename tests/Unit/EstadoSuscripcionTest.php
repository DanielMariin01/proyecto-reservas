<?php

declare(strict_types=1);

use App\Enums\EstadoSuscripcion;

test('la cadena de transiciones automaticas sigue activo, vencido, suspendido, cancelado', function (): void {
    expect(EstadoSuscripcion::Activo->siguienteEstadoAutomatico())->toBe(EstadoSuscripcion::Vencido);
    expect(EstadoSuscripcion::Vencido->siguienteEstadoAutomatico())->toBe(EstadoSuscripcion::Suspendido);
    expect(EstadoSuscripcion::Suspendido->siguienteEstadoAutomatico())->toBe(EstadoSuscripcion::Cancelado);
    expect(EstadoSuscripcion::Cancelado->siguienteEstadoAutomatico())->toBeNull();
});

test('solo activo y vencido permiten el acceso al panel y las reservas', function (): void {
    expect(EstadoSuscripcion::Activo->permiteAccesoAlPanel())->toBeTrue();
    expect(EstadoSuscripcion::Vencido->permiteAccesoAlPanel())->toBeTrue();
    expect(EstadoSuscripcion::Suspendido->permiteAccesoAlPanel())->toBeFalse();
    expect(EstadoSuscripcion::Cancelado->permiteAccesoAlPanel())->toBeFalse();

    expect(EstadoSuscripcion::Activo->permiteReservas())->toBeTrue();
    expect(EstadoSuscripcion::Suspendido->permiteReservas())->toBeFalse();
});
