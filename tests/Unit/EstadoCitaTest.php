<?php

declare(strict_types=1);

use App\Enums\EstadoCitaValor;
use App\Estados\Cita\EstadoCancelada;
use App\Estados\Cita\EstadoCompletada;
use App\Estados\Cita\EstadoConfirmada;
use App\Estados\Cita\EstadoEnAtencion;
use App\Estados\Cita\EstadoNoAsistio;
use App\Estados\Cita\EstadoPendiente;
use App\Estados\Cita\EstadoReprogramada;
use App\Estados\Cita\FabricaEstadoCita;

test('pendiente puede pasar a confirmada, cancelada o reprogramada, nada mas', function (): void {
    $estado = new EstadoPendiente;

    expect($estado->puedeTransicionarA(EstadoCitaValor::Confirmada))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::Cancelada))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::Reprogramada))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::Completada))->toBeFalse();
    expect($estado->puedeTransicionarA(EstadoCitaValor::EnAtencion))->toBeFalse();
    expect($estado->estaActiva())->toBeTrue();
    expect($estado->esFinal())->toBeFalse();
});

test('confirmada puede pasar a en atencion, cancelada, no asistio o reprogramada', function (): void {
    $estado = new EstadoConfirmada;

    expect($estado->puedeTransicionarA(EstadoCitaValor::EnAtencion))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::Cancelada))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::NoAsistio))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::Reprogramada))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::Completada))->toBeFalse();
});

test('en atencion solo puede pasar a completada o cancelada', function (): void {
    $estado = new EstadoEnAtencion;

    expect($estado->puedeTransicionarA(EstadoCitaValor::Completada))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::Cancelada))->toBeTrue();
    expect($estado->puedeTransicionarA(EstadoCitaValor::NoAsistio))->toBeFalse();
    expect($estado->puedeTransicionarA(EstadoCitaValor::Confirmada))->toBeFalse();
});

test('completada, cancelada y no asistio son finales y no aceptan ninguna transicion', function (): void {
    foreach ([new EstadoCompletada, new EstadoCancelada, new EstadoNoAsistio] as $estado) {
        expect($estado->esFinal())->toBeTrue();
        expect($estado->estaActiva())->toBeFalse();

        foreach (EstadoCitaValor::cases() as $destino) {
            expect($estado->puedeTransicionarA($destino))->toBeFalse();
        }
    }
});

test('reprogramada solo puede pasar a confirmada', function (): void {
    $estado = new EstadoReprogramada;

    expect($estado->puedeTransicionarA(EstadoCitaValor::Confirmada))->toBeTrue();
    expect($estado->estaActiva())->toBeTrue();

    foreach (EstadoCitaValor::cases() as $destino) {
        if ($destino !== EstadoCitaValor::Confirmada) {
            expect($estado->puedeTransicionarA($destino))->toBeFalse();
        }
    }
});

test('la fabrica reconstruye la clase de estado correcta para cada valor', function (): void {
    $fabrica = new FabricaEstadoCita;

    foreach (EstadoCitaValor::cases() as $valor) {
        expect($fabrica->paraValor($valor)->valor())->toBe($valor);
    }
});

test('los valores activos son pendiente, confirmada, en atencion y reprogramada', function (): void {
    $activos = (new FabricaEstadoCita)->valoresActivos();

    expect($activos)->toEqualCanonicalizing([
        EstadoCitaValor::Pendiente,
        EstadoCitaValor::Confirmada,
        EstadoCitaValor::EnAtencion,
        EstadoCitaValor::Reprogramada,
    ]);
});
