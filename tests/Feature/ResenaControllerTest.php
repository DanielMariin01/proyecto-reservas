<?php

declare(strict_types=1);

use App\Enums\EstadoResenaValor;
use App\Models\Negocio;
use App\Models\Resena;
use App\Models\Usuario;

test('un usuario normal no puede ver la pagina de resenas', function (): void {
    $usuario = Usuario::factory()->create();

    $this->actingAs($usuario)->get('/admin/resenas')->assertForbidden();
});

test('el super admin ve la pagina de resenas', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)->get('/admin/resenas')->assertOk()->assertSee('Reseñas');
});

test('la api lista las resenas ordenadas por fecha descendente', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $antigua = Resena::factory()->create(['created_at' => now()->subDays(2)]);
    $reciente = Resena::factory()->create(['created_at' => now()]);

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/resenas');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos[0]['id'])->toBe($reciente->id);
    expect($datos[1]['id'])->toBe($antigua->id);
});

test('la api filtra por negocio y por estado', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $negocio = Negocio::factory()->create();
    $resenaDelNegocio = Resena::factory()->create(['negocio_id' => $negocio->id]);
    Resena::factory()->create();
    Resena::factory()->oculta()->create(['negocio_id' => $negocio->id]);

    $respuesta = $this->actingAs($superAdmin)->getJson("/admin/api/resenas?negocioId={$negocio->id}&estado=publicada");

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos)->toHaveCount(1);
    expect($datos[0]['id'])->toBe($resenaDelNegocio->id);
});

test('ocultar una resena publicada la marca como oculta', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $resena = Resena::factory()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/resenas/{$resena->id}/ocultar");

    $respuesta->assertOk();
    expect($resena->refresh()->estado)->toBe(EstadoResenaValor::Oculta);
    expect($resena->oculta_por_id)->toBe($superAdmin->id);
});

test('volver a publicar una resena oculta la marca como publicada', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();
    $resena = Resena::factory()->oculta()->create();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/resenas/{$resena->id}/mostrar");

    $respuesta->assertOk();
    expect($resena->refresh()->estado)->toBe(EstadoResenaValor::Publicada);
});
