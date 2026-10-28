<?php

declare(strict_types=1);

use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Notifications\RecordatorioDeCitaCliente;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

/**
 * @return array{negocio: Negocio, cliente: Cliente, cita: Cita}
 */
function negocioConCitaEnVeinticuatroHoras(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-01 10:00:00', 'UTC'));

    $negocio = Negocio::factory()->create();
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-02 10:05:00', 'UTC'),
        'fin' => CarbonImmutable::parse('2026-12-02 10:50:00', 'UTC'),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    return ['negocio' => $negocio, 'cliente' => $cliente, 'cita' => $cita];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('envia el recordatorio a las citas que caen en la ventana de 24 horas', function (): void {
    Notification::fake();
    ['cliente' => $cliente, 'cita' => $cita] = negocioConCitaEnVeinticuatroHoras();

    $this->artisan('citas:enviar-recordatorios')->assertSuccessful();

    Notification::assertSentOnDemand(
        RecordatorioDeCitaCliente::class,
        fn ($notificacion, $canales, $notifiable): bool => $notifiable->routes['mail'] === $cliente->correo,
    );
    expect($cita->refresh()->recordatorio_enviado_en)->not->toBeNull();
});

test('no reenvia el recordatorio si el comando corre dos veces', function (): void {
    Notification::fake();
    negocioConCitaEnVeinticuatroHoras();

    $this->artisan('citas:enviar-recordatorios');
    $this->artisan('citas:enviar-recordatorios');

    Notification::assertSentOnDemandTimes(RecordatorioDeCitaCliente::class, 1);
});

test('no envia recordatorio a una cita fuera de la ventana de 24 horas', function (): void {
    Notification::fake();
    ['cita' => $cita] = negocioConCitaEnVeinticuatroHoras();
    $cita->update(['inicio' => CarbonImmutable::parse('2026-12-05 10:00:00', 'UTC')]);

    $this->artisan('citas:enviar-recordatorios');

    Notification::assertSentOnDemandTimes(RecordatorioDeCitaCliente::class, 0);
});

test('no envia recordatorio a una cita ya cancelada', function (): void {
    Notification::fake();
    ['cita' => $cita] = negocioConCitaEnVeinticuatroHoras();
    $cita->update(['estado' => EstadoCitaValor::Cancelada]);

    $this->artisan('citas:enviar-recordatorios');

    Notification::assertSentOnDemandTimes(RecordatorioDeCitaCliente::class, 0);
});
