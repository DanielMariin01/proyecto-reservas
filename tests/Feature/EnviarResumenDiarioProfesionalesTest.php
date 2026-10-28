<?php

declare(strict_types=1);

use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Notifications\ResumenDiarioDeCitasParaProfesional;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('envia el resumen de hoy solo a profesionales con usuario vinculado', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-01 12:00:00', 'America/Bogota'));
    Notification::fake();

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesionalConUsuario = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $usuarioProfesional->id]);
    $profesionalSinUsuario = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => null]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesionalConUsuario->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-01 15:00:00', 'America/Bogota')->utc(),
        'fin' => CarbonImmutable::parse('2026-12-01 15:45:00', 'America/Bogota')->utc(),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesionalSinUsuario->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-01 16:00:00', 'America/Bogota')->utc(),
        'fin' => CarbonImmutable::parse('2026-12-01 16:45:00', 'America/Bogota')->utc(),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    $this->artisan('citas:enviar-resumen-diario-profesionales')->assertSuccessful();

    Notification::assertSentTo(
        $usuarioProfesional,
        ResumenDiarioDeCitasParaProfesional::class,
        fn ($notificacion): bool => $notificacion->toMail($usuarioProfesional)->subject === 'Tus citas de hoy: 1',
    );
    Notification::assertCount(1);
});

test('no se cuentan citas de otro dia en el resumen', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-01 12:00:00', 'America/Bogota'));
    Notification::fake();

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    $profesional = Profesional::factory()->create(['negocio_id' => $negocio->id, 'usuario_id' => $usuarioProfesional->id]);
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::parse('2026-12-03 10:00:00', 'America/Bogota')->utc(),
        'fin' => CarbonImmutable::parse('2026-12-03 10:45:00', 'America/Bogota')->utc(),
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    $this->artisan('citas:enviar-resumen-diario-profesionales');

    Notification::assertSentTo(
        $usuarioProfesional,
        ResumenDiarioDeCitasParaProfesional::class,
        fn ($notificacion): bool => $notificacion->toMail($usuarioProfesional)->subject === 'Tus citas de hoy: 0',
    );
});
