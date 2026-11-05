<?php

declare(strict_types=1);

use App\Actions\CambiarEstadoCita;
use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Notifications\InvitacionADejarResena;
use Illuminate\Support\Facades\Notification;

/**
 * @return array{cita: Cita, cliente: Cliente}
 */
function citaConfirmadaConCliente(): array
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
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    return ['cita' => $cita, 'cliente' => $cliente];
}

test('completar una cita invita al cliente a dejar una reseña', function (): void {
    Notification::fake();
    ['cita' => $cita, 'cliente' => $cliente] = citaConfirmadaConCliente();

    app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::EnAtencion);
    app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::Completada);

    Notification::assertSentOnDemand(
        InvitacionADejarResena::class,
        fn ($notificacion, $canales, $notifiable): bool => $notifiable->routes['mail'] === $cliente->correo,
    );
});

test('no se invita a reseñar si el cliente no dejo correo', function (): void {
    Notification::fake();
    ['cita' => $cita, 'cliente' => $cliente] = citaConfirmadaConCliente();
    $cliente->update(['correo' => null]);

    app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::EnAtencion);
    app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::Completada);

    Notification::assertSentOnDemandTimes(InvitacionADejarResena::class, 0);
});

test('pasar a en atencion no invita a dejar una reseña', function (): void {
    Notification::fake();
    ['cita' => $cita] = citaConfirmadaConCliente();

    app(CambiarEstadoCita::class)->ejecutar($cita, EstadoCitaValor::EnAtencion);

    Notification::assertNothingSent();
});
