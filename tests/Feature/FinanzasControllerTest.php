<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Gasto;
use App\Models\Negocio;
use App\Models\Pago;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('el dueno puede ver finanzas', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $this->actingAs($dueno)->get("/app/{$negocio->slug}/finanzas")->assertOk();
    $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/finanzas")->assertOk();
});

test('un profesional no puede ver finanzas', function (): void {
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $this->actingAs($profesionalUsuario)->get("/app/{$negocio->slug}/finanzas")->assertForbidden();
    $this->actingAs($profesionalUsuario)->getJson("/app/{$negocio->slug}/api/finanzas")->assertForbidden();
});

test('el rango por defecto es este mes y agrupa los ingresos por metodo de pago', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-15 10:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Pago::factory()->create(['negocio_id' => $negocio->id, 'metodo' => 'efectivo', 'valor' => 20000, 'propina' => 2000]);
    Pago::factory()->create(['negocio_id' => $negocio->id, 'metodo' => 'nequi', 'valor' => 30000, 'propina' => 0]);
    // Fuera del mes actual, no debe contar.
    Pago::factory()->create(['negocio_id' => $negocio->id, 'valor' => 99999, 'created_at' => CarbonImmutable::parse('2026-10-01')]);
    // Anulado, no debe contar.
    Pago::factory()->anulado()->create(['negocio_id' => $negocio->id, 'valor' => 99999]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/finanzas");

    $respuesta->assertOk();
    expect($respuesta->json('rango'))->toBe('mes');
    expect($respuesta->json('ingresosBrutos'))->toBe(52000);
    expect($respuesta->json('totalPropinas'))->toBe(2000);
    expect($respuesta->json('ingresosPorMetodo.efectivo.total'))->toBe(22000);
    expect($respuesta->json('ingresosPorMetodo.nequi.total'))->toBe(30000);
});

test('un gasto resta del neto y queda en el rango por fecha', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-15 10:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Pago::factory()->create(['negocio_id' => $negocio->id, 'valor' => 50000, 'propina' => 0]);
    Gasto::factory()->create(['negocio_id' => $negocio->id, 'monto' => 15000, 'fecha' => '2026-11-10']);
    // Fuera del mes actual, no debe contar.
    Gasto::factory()->create(['negocio_id' => $negocio->id, 'monto' => 99999, 'fecha' => '2026-10-31']);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/finanzas");

    $respuesta->assertOk();
    expect($respuesta->json('totalGastos'))->toBe(15000);
    expect($respuesta->json('neto'))->toBe(35000);
});

test('finanzas no mezcla pagos ni gastos de otro negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $otroNegocio = Negocio::factory()->create();
    Pago::factory()->create(['negocio_id' => $otroNegocio->id, 'valor' => 99999]);
    Gasto::factory()->create(['negocio_id' => $otroNegocio->id, 'monto' => 99999]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/finanzas");

    $respuesta->assertOk();
    expect($respuesta->json('ingresosBrutos'))->toBe(0);
    expect($respuesta->json('totalGastos'))->toBe(0);
});

test('el rango de ultimos 15 dias incluye hoy y excluye lo mas viejo', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-15 10:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Pago::factory()->create(['negocio_id' => $negocio->id, 'valor' => 10000, 'created_at' => CarbonImmutable::now('America/Bogota')]);
    // El rango son los ultimos 15 dias (1 al 15 de noviembre): el 31 de
    // octubre queda justo un dia antes de que empiece, debe excluirse.
    Pago::factory()->create(['negocio_id' => $negocio->id, 'valor' => 20000, 'created_at' => CarbonImmutable::parse('2026-10-31 08:00:00', 'America/Bogota')]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/finanzas?rango=quince_dias");

    $respuesta->assertOk();
    expect($respuesta->json('ingresosBrutos'))->toBe(10000);
});

test('el rango de este anio incluye todo el anio actual', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-15 10:00:00', 'America/Bogota'));

    $negocio = Negocio::factory()->create(['zona_horaria' => 'America/Bogota']);
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Pago::factory()->create(['negocio_id' => $negocio->id, 'valor' => 10000, 'created_at' => CarbonImmutable::parse('2026-02-01 08:00:00', 'America/Bogota')]);
    Pago::factory()->create(['negocio_id' => $negocio->id, 'valor' => 20000, 'created_at' => CarbonImmutable::parse('2025-12-31 08:00:00', 'America/Bogota')]);

    $respuesta = $this->actingAs($dueno)->getJson("/app/{$negocio->slug}/api/finanzas?rango=anio");

    $respuesta->assertOk();
    expect($respuesta->json('ingresosBrutos'))->toBe(10000);
});

test('el dueno puede registrar y eliminar un gasto', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->postJson("/app/{$negocio->slug}/api/finanzas/gastos", [
        'monto' => 25000,
        'descripcion' => 'Insumos de peluquería',
        'fecha' => now()->toDateString(),
    ]);

    $respuesta->assertCreated();
    $gastoId = $respuesta->json('data.id');
    $this->assertDatabaseHas('gastos', ['id' => $gastoId, 'negocio_id' => $negocio->id, 'monto' => 25000]);

    $this->actingAs($dueno)->deleteJson("/app/{$negocio->slug}/api/finanzas/gastos/{$gastoId}")->assertOk();
    $this->assertDatabaseMissing('gastos', ['id' => $gastoId]);
});

test('un profesional no puede registrar gastos', function (): void {
    $negocio = Negocio::factory()->create();
    $profesionalUsuario = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    $this->actingAs($profesionalUsuario)->postJson("/app/{$negocio->slug}/api/finanzas/gastos", [
        'monto' => 25000,
        'descripcion' => 'Insumos',
        'fecha' => now()->toDateString(),
    ])->assertForbidden();
});

test('un dueno no puede eliminar un gasto de otro negocio aunque adivine el id', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);
    $gastoB = Gasto::factory()->create(['negocio_id' => $negocioB->id]);

    $this->actingAs($duenoA)->deleteJson("/app/{$negocioA->slug}/api/finanzas/gastos/{$gastoB->id}")->assertNotFound();

    $this->assertDatabaseHas('gastos', ['id' => $gastoB->id]);
});
