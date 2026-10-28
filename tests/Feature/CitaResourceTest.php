<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\DiaSemana;
use App\Enums\RolNegocio;
use App\Filament\App\Resources\Citas\Pages\CreateCita;
use App\Filament\App\Resources\Citas\Pages\ListCitas;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\HorarioTrabajo;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Livewire\Livewire;

/**
 * @return array{negocio: Negocio, profesional: Profesional, servicio: Servicio, fecha: CarbonImmutable}
 */
function negocioListoParaCrearCitaDesdeElPanel(): array
{
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-01 00:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create([
        'negocio_id' => $negocio->id,
        'duracion_minutos' => 60,
        'intervalo_franjas_minutos' => 60,
        'visible' => true,
    ]);
    $servicio->profesionales()->attach($profesional->id);

    $fecha = CarbonImmutable::now('America/Bogota')->next(CarbonImmutable::TUESDAY);

    HorarioTrabajo::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesional->id,
        'dia_semana' => DiaSemana::from($fecha->dayOfWeek),
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    return ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha];
}

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('el dueno puede ver la lista de citas', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/app/{$negocio->slug}/citas");

    $respuesta->assertOk();
});

test('un profesional no puede crear citas desde el panel', function (): void {
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $respuesta = $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/citas/create");

    $respuesta->assertForbidden();
});

test('el dueno puede crear una cita desde el panel con un profesional especifico', function (): void {
    ['negocio' => $negocio, 'profesional' => $profesional, 'servicio' => $servicio, 'fecha' => $fecha] = negocioListoParaCrearCitaDesdeElPanel();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(CreateCita::class)
        ->fillForm([
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'fecha' => $fecha->toDateString(),
            'hora' => '10:00',
            'nombre_cliente' => 'Ana',
            'celular_cliente' => '3001234567',
            'correo_cliente' => 'ana@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $cita = Cita::query()->where('profesional_id', $profesional->id)->first();

    expect($cita)->not->toBeNull();
    expect(Cliente::query()->where('celular', '3001234567')->count())->toBe(1);
});

test('el dueno puede crear una cita con cualquier profesional disponible', function (): void {
    ['negocio' => $negocio, 'servicio' => $servicio, 'fecha' => $fecha] = negocioListoParaCrearCitaDesdeElPanel();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(CreateCita::class)
        ->fillForm([
            'servicio_id' => $servicio->id,
            'fecha' => $fecha->toDateString(),
            'hora' => '10:00',
            'nombre_cliente' => 'Ana',
            'celular_cliente' => '3001234567',
            'correo_cliente' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Cita::query()->count())->toBe(1);
});

test('un profesional solo ve sus propias citas en la lista', function (): void {
    $negocio = Negocio::factory()->create();
    $usuarioProfesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $profesionalPropio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $profesionalPropio->update(['usuario_id' => $usuarioProfesional->id]);
    $otroProfesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Otro'));

    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaPropia = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $profesionalPropio->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
    ]);
    $citaAjena = Cita::factory()->create([
        'negocio_id' => $negocio->id,
        'profesional_id' => $otroProfesional->id,
        'servicio_id' => $servicio->id,
        'cliente_id' => $cliente->id,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($usuarioProfesional)
        ->test(ListCitas::class)
        ->assertCanSeeTableRecords([$citaPropia])
        ->assertCanNotSeeTableRecords([$citaAjena]);
});

test('el dueno ve todas las citas del negocio en la lista', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $profesionalA = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'A'));
    $profesionalB = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'B'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id]);

    $citaA = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesionalA->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);
    $citaB = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesionalB->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($negocio, isQuiet: true);

    Livewire::actingAs($dueno)
        ->test(ListCitas::class)
        ->assertCanSeeTableRecords([$citaA, $citaB]);
});
