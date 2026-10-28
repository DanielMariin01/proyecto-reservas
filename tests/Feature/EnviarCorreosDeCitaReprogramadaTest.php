<?php

declare(strict_types=1);

use App\Actions\ReprogramarCita;
use App\DatosTransferencia\DatosReprogramacion;
use App\Enums\DiaSemana;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Events\CitaReprogramada;
use App\Listeners\EnviarCorreosDeCitaReprogramada;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Notifications\AvisoDeCitaReprogramadaParaNegocio;
use App\Notifications\CitaReprogramadaCliente;
use Carbon\CarbonImmutable;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

/**
 * @return array{negocio: Negocio, profesional: Profesional, cliente: Cliente, cita: Cita, fecha: CarbonImmutable}
 */
function negocioConCitaReprogramableParaCorreos(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-01 00:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create([
        'zona_horaria' => 'America/Bogota',
        'horas_minimas_reprogramacion' => 2,
        'maximo_reprogramaciones' => 2,
    ]);
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $profesionalUsuario->id]);
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
    ]);

    $fecha = CarbonImmutable::now('America/Bogota')->next(CarbonImmutable::TUESDAY);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => $fecha->copy()->setTime(10, 0)->utc(),
        'fin' => $fecha->copy()->setTime(11, 0)->utc(),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'cliente' => $cliente, 'cita' => $cita, 'fecha' => $fecha];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('CitaReprogramada encola el correo en vez de enviarlo de forma sincrona', function (): void {
    Queue::fake();
    ['cita' => $cita] = negocioConCitaReprogramableParaCorreos();

    CitaReprogramada::dispatch($cita, $cita->inicio);

    Queue::assertPushed(CallQueuedListener::class, fn ($trabajo): bool => $trabajo->class === EnviarCorreosDeCitaReprogramada::class);
});

test('al reprogramar una cita se notifica al cliente, al profesional y al dueno', function (): void {
    Notification::fake();
    [
        'negocio' => $negocio,
        'profesional' => $profesional,
        'cliente' => $cliente,
        'cita' => $cita,
        'fecha' => $fecha,
    ] = negocioConCitaReprogramableParaCorreos();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion($cita, $fecha->copy()->setTime(11, 0)->utc()));

    Notification::assertSentOnDemand(
        CitaReprogramadaCliente::class,
        fn ($notificacion, $canales, $notifiable): bool => $notifiable->routes['mail'] === $cliente->correo,
    );
    Notification::assertSentTo($dueno, AvisoDeCitaReprogramadaParaNegocio::class);
    Notification::assertSentTo($profesional->usuario, AvisoDeCitaReprogramadaParaNegocio::class);
});
