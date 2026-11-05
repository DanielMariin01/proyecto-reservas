<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Filament\App\Pages\AgendaDelDia;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Livewire\Livewire;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('el dueno puede ver la agenda del dia', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/agenda");

    $respuesta->assertOk();
});

test('la agenda muestra las citas del dia agrupadas por profesional', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Corte']);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Ana Pérez']);

    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/agenda");

    $respuesta->assertOk();
    $respuesta->assertSee('Japo');
    $respuesta->assertSee('Ana Pérez');
    $respuesta->assertSee('Corte');
});

test('un profesional no puede ver la agenda general', function (): void {
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $respuesta = $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/agenda");

    $respuesta->assertForbidden();
});

test('el dueno puede cancelar una cita desde la agenda del dia', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Pendiente,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    // El context con recordKey es lo que el botón real envía (Action::getContext()
    // lo arma solo a partir de ->record($cita)); nunca pases esto como "arguments",
    // porque el clic real no usa ese canal (ver Action::getJsClickHandler()).
    Livewire::actingAs($dueno)
        ->test(AgendaDelDia::class)
        ->mountAction(['name' => 'cancelar', 'context' => ['recordKey' => (string) $cita->id]])
        ->setActionData(['motivo' => 'El cliente no puede asistir'])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Cancelada);
});

test('el dueno puede completar una cita con pago desde la agenda del dia', function (): void {
    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::EnAtencion,
        'precio' => 30000,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(AgendaDelDia::class)
        ->mountAction(['name' => 'completarConPago', 'context' => ['recordKey' => (string) $cita->id]])
        ->assertSchemaStateSet(['valor' => 30000])
        ->setActionData(['metodo' => 'efectivo', 'valor' => 30000, 'propina' => 0])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Completada);
});
