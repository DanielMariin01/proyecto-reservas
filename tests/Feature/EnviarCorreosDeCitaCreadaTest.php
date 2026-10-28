<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Events\CitaCreada;
use App\Listeners\EnviarCorreosDeCitaCreada;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Notifications\AvisoDeCitaNuevaParaNegocio;
use App\Notifications\ConfirmacionDeCitaCliente;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

/**
 * @return array{negocio: Negocio, profesional: Profesional, cliente: Cliente, cita: Cita}
 */
function negocioConCitaRecienCreada(): array
{
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $profesionalUsuario->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'cliente' => $cliente, 'cita' => $cita];
}

test('CitaCreada encola el correo en vez de enviarlo de forma sincrona', function (): void {
    Queue::fake();
    ['cita' => $cita] = negocioConCitaRecienCreada();

    CitaCreada::dispatch($cita);

    Queue::assertPushed(CallQueuedListener::class, fn ($trabajo): bool => $trabajo->class === EnviarCorreosDeCitaCreada::class);
});

test('al crear una cita se notifica al cliente, al profesional y al dueno', function (): void {
    Notification::fake();
    ['negocio' => $negocio, 'profesional' => $profesional, 'cliente' => $cliente, 'cita' => $cita] = negocioConCitaRecienCreada();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    CitaCreada::dispatch($cita);

    Notification::assertSentOnDemand(
        ConfirmacionDeCitaCliente::class,
        fn ($notificacion, $canales, $notifiable): bool => $notifiable->routes['mail'] === $cliente->correo,
    );
    Notification::assertSentTo($dueno, AvisoDeCitaNuevaParaNegocio::class);
    Notification::assertSentTo($profesional->usuario, AvisoDeCitaNuevaParaNegocio::class);
});

test('no envia correo al cliente si no dejo correo', function (): void {
    Notification::fake();
    ['cita' => $cita, 'cliente' => $cliente] = negocioConCitaRecienCreada();
    $cliente->update(['correo' => null]);
    $cita->refresh();

    CitaCreada::dispatch($cita);

    Notification::assertSentOnDemandTimes(ConfirmacionDeCitaCliente::class, 0);
});
