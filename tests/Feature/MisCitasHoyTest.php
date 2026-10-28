<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Filament\App\Pages\MisCitasHoy;
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

test('un usuario sin profesional vinculado no puede ver Mis citas de hoy', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/mis-citas-hoy");

    $respuesta->assertForbidden();
});

test('el profesional ve solo sus citas de hoy, no las de otro dia ni las de otro profesional', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Corte']);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Ana Pérez']);

    $citaDeHoy = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->addDay()->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->addDay()->setTime(11, 0)->utc(),
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(14, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(15, 0)->utc(),
    ]);

    $respuesta = $this->actingAs($usuarioProfesional)->get("/app/{$negocio->slug}/mis-citas-hoy");

    $respuesta->assertOk();
    $respuesta->assertSee('Ana Pérez');

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    $citasVisibles = Livewire::actingAs($usuarioProfesional)->test(MisCitasHoy::class)->instance()->citasDeHoy();

    expect($citasVisibles->pluck('id')->all())->toBe([$citaDeHoy->id]);
});

test('marcar en atencion y despues completada actualiza el estado', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesional->update(['usuario_id' => $usuarioProfesional->id]);

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($usuarioProfesional)
        ->test(MisCitasHoy::class)
        ->call('marcarEnAtencion', $cita->id);

    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::EnAtencion);

    Livewire::actingAs($usuarioProfesional)
        ->test(MisCitasHoy::class)
        ->call('marcarCompletada', $cita->id);

    expect($cita->refresh()->estado)->toBe(EstadoCitaValor::Completada);
});

test('un profesional no puede cambiar el estado de la cita de otro profesional', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);
    app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'))
        ->update(['usuario_id' => $usuarioProfesional->id]);

    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaAjena = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($usuarioProfesional)
        ->test(MisCitasHoy::class)
        ->call('marcarEnAtencion', $citaAjena->id)
        ->assertStatus(404);
});
