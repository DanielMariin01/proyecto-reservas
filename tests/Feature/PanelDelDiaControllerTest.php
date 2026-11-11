<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Servicio;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('el dueno puede ver el panel del dia', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/panel-del-dia")->assertOk();
    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/panel-del-dia")->assertOk();
});

test('un profesional no puede ver el panel del dia', function (): void {
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/panel-del-dia")->assertForbidden();
    $this->actingAs($profesionalUsuario)->getJson("/app/{$negocio->slug}/api/panel-del-dia")->assertForbidden();
});

test('la api del panel del dia solo cuenta las citas e ingresos del negocio actual', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 08:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);
    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $negocio, nombre: 'Japo'));
    $servicio = Servicio::factory()->create(['negocio_id' => $negocio->id]);
    $cliente = Cliente::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Juan']);

    $cita = Cita::factory()->create([
        'negocio_id' => $negocio->id, 'profesional_id' => $profesional->id, 'servicio_id' => $servicio->id, 'cliente_id' => $cliente->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);
    Pago::factory()->create(['negocio_id' => $negocio->id, 'cita_id' => $cita->id, 'valor' => 20000, 'propina' => 0]);

    $otroNegocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $profesionalOtroNegocio = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(negocio: $otroNegocio, nombre: 'Ajeno'));
    $servicioOtro = Servicio::factory()->create(['negocio_id' => $otroNegocio->id]);
    $clienteOtro = Cliente::factory()->create(['negocio_id' => $otroNegocio->id]);
    Cita::factory()->create([
        'negocio_id' => $otroNegocio->id, 'profesional_id' => $profesionalOtroNegocio->id, 'servicio_id' => $servicioOtro->id, 'cliente_id' => $clienteOtro->id,
        'inicio' => CarbonImmutable::now('America/Bogota')->setTime(10, 0)->utc(),
        'fin' => CarbonImmutable::now('America/Bogota')->setTime(11, 0)->utc(),
    ]);
    Pago::factory()->create(['negocio_id' => $otroNegocio->id, 'valor' => 99999]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/panel-del-dia");

    $respuesta->assertOk();
    expect($respuesta->json('totalCitasHoy'))->toBe(1);
    expect($respuesta->json('ingresosRegistrados'))->toBe(20000);
    expect($respuesta->json('citas.0.clienteNombre'))->toBe('Juan');
});
