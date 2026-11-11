<?php

declare(strict_types=1);

use App\Models\Negocio;
use App\Models\RegistroError;
use App\Models\Usuario;

test('un usuario normal no puede ver la pagina de registro de errores', function (): void {
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($usuario)->get('/admin/registro-errores');

    $respuesta->assertForbidden();
});

test('el super admin ve la pagina de registro de errores', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->get('/admin/registro-errores');

    $respuesta->assertOk()->assertSee('Registro de errores');
});

test('la api filtra por negocio y por defecto solo muestra las ultimas 24 horas', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();

    $reciente = RegistroError::create([
        'negocio_id' => $negocioA->id,
        'clase_excepcion' => 'RuntimeException',
        'mensaje' => 'algo fallo',
        'codigo_referencia' => 'REF-1',
        'estado_http' => 500,
    ]);

    $viejo = RegistroError::create([
        'negocio_id' => $negocioA->id,
        'clase_excepcion' => 'RuntimeException',
        'mensaje' => 'algo viejo',
        'codigo_referencia' => 'REF-2',
        'estado_http' => 500,
    ]);
    $viejo->created_at = now()->subDays(2);
    $viejo->save();

    RegistroError::create([
        'negocio_id' => $negocioB->id,
        'clase_excepcion' => 'RuntimeException',
        'mensaje' => 'de otro negocio',
        'codigo_referencia' => 'REF-3',
        'estado_http' => 500,
    ]);

    $respuesta = $this->actingAs($superAdmin)->getJson("/admin/api/registro-errores?negocioId={$negocioA->id}");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['id'])->toBe($reciente->id);
});

test('ultimas24h en 0 incluye registros mas antiguos', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $viejo = RegistroError::create([
        'clase_excepcion' => 'RuntimeException',
        'mensaje' => 'algo viejo',
        'codigo_referencia' => 'REF-2',
        'estado_http' => 500,
    ]);
    $viejo->created_at = now()->subDays(2);
    $viejo->save();

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/registro-errores?ultimas24h=0');

    $respuesta->assertOk();
    expect($respuesta->json('data'))->toHaveCount(1);
});

test('la api de opciones de negocio devuelve la lista ordenada por nombre', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    Negocio::factory()->create(['nombre' => 'Zeta']);
    Negocio::factory()->create(['nombre' => 'Alfa']);

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/negocios-opciones');

    $respuesta->assertOk();
    $nombres = collect($respuesta->json('data'))->pluck('nombre');

    expect($nombres->first())->toBe('Alfa');
});
