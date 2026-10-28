<?php

declare(strict_types=1);

use App\Actions\CancelarCita;
use App\Enums\RolNegocio;
use App\Events\CitaCancelada;
use App\Listeners\EnviarCorreosDeCitaCancelada;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Notifications\AvisoDeCitaCanceladaParaNegocio;
use App\Notifications\CitaCanceladaCliente;
use Carbon\CarbonImmutable;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

/**
 * @return array{negocio: Negocio, profesional: Profesional, cliente: Cliente, cita: Cita}
 */
function negocioConCitaCancelable(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-01 10:00:00', 'UTC'));

    $negocio = Negocio::factory()->create(['horas_minimas_cancelacion' => 2]);
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $profesionalUsuario->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-05 11:00:00', 'UTC'),
        'fin' => CarbonImmutable::parse('2026-12-05 11:45:00', 'UTC'),
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'cliente' => $cliente, 'cita' => $cita];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('CitaCancelada encola el correo en vez de enviarlo de forma sincrona', function (): void {
    Queue::fake();
    ['cita' => $cita] = negocioConCitaCancelable();

    CitaCancelada::dispatch($cita, null);

    Queue::assertPushed(CallQueuedListener::class, fn ($trabajo): bool => $trabajo->class === EnviarCorreosDeCitaCancelada::class);
});

test('al cancelar una cita se notifica al cliente, al profesional y al dueno', function (): void {
    Notification::fake();
    ['negocio' => $negocio, 'profesional' => $profesional, 'cliente' => $cliente, 'cita' => $cita] = negocioConCitaCancelable();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    app(CancelarCita::class)->ejecutar($cita, 'Ya no puedo ir', canceladoPor: $dueno);

    Notification::assertSentOnDemand(
        CitaCanceladaCliente::class,
        fn ($notificacion, $canales, $notifiable): bool => $notifiable->routes['mail'] === $cliente->correo,
    );
    Notification::assertSentTo($dueno, AvisoDeCitaCanceladaParaNegocio::class);
    Notification::assertSentTo($profesional->usuario, AvisoDeCitaCanceladaParaNegocio::class);
});
