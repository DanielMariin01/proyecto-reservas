<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\DiaSemana;
use App\Enums\EstadoCitaValor;
use App\Enums\RolNegocio;
use App\Filament\App\Pages\PanelDelDia;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Livewire\Livewire;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('el dueno puede ver el panel del dia', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/panel-del-dia");

    $respuesta->assertOk();
});

test('un profesional no puede ver el panel del dia', function (): void {
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $respuesta = $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/panel-del-dia");

    $respuesta->assertForbidden();
});

test('citasDeHoy solo cuenta las de hoy y no las de otro negocio', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaDeHoy = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);
    Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->addDay()->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->addDay()->setTime(11, 0)->utc(),
    ]);

    $otroNegocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $profesionalOtroNegocio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $otroNegocio, nombre: 'Ajeno'));
    $servicioOtro = Servicio::factory()->create(['negocio_id' => $otroNegocio->id]);
    $clienteOtro = Cliente::factory()->create(['negocio_id' => $otroNegocio->id]);
    Cita::factory()->create([
        'negocio_id' => $otroNegocio->id, 'profesional_id' => $profesionalOtroNegocio->id, 'servicio_id' => $servicioOtro->id, 'cliente_id' => $clienteOtro->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    $citas = Livewire::actingAs($dueno)->test(PanelDelDia::class)->instance()->citasDeHoy();

    expect($citas->pluck('id')->all())->toBe([$citaDeHoy->id]);
});

test('ingresosRegistrados suma solo los pagos activos registrados hoy del negocio actual', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaCompletada = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Completada,
    ]);

    Pago::factory()->create(['negocio_id' => $negocio->id, 'cita_id' => $citaCompletada->id, 'valor' => 20000, 'propina' => 2000]);
    Pago::factory()->anulado()->create(['negocio_id' => $negocio->id, 'cita_id' => $citaCompletada->id, 'valor' => 99999]);

    $otroNegocio = Negocio::factory()->create();
    Pago::factory()->create(['negocio_id' => $otroNegocio->id, 'valor' => 50000]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    $ingresos = Livewire::actingAs($dueno)->test(PanelDelDia::class)->instance()->ingresosRegistrados();

    expect($ingresos)->toBe(22000);
});

test('huecosLibres resta las citas activas de los bloques libres del profesional', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id, 'duracion_minutos' => 60]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from(CarbonImmutable::now('America/Bogota')->dayOfWeek),
        'hora_inicio' => '09:00:00',
        'hora_fin' => '12:00:00',
    ]);

    Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
        'estado' => EstadoCitaValor::Confirmada,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    $huecos = Livewire::actingAs($dueno)->test(PanelDelDia::class)->instance()->huecosLibres();
    $bloquesDeJapo = $huecos->firstWhere(fn ($columna) => $columna['profesional']->id === $profesional->id)['bloques'];
    $horas = $bloquesDeJapo->map(fn ($bloque) => $bloque->inicio->setTimezone('America/Bogota')->format('H:i').'-'.$bloque->fin->setTimezone('America/Bogota')->format('H:i'))->values()->all();

    expect($horas)->toBe(['09:00-10:00', '11:00-12:00']);
});
