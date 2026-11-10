<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Http\Middleware\ResolverNegocioDesdeRuta;
use App\Models\Negocio;
use App\Models\Usuario;
use App\Negocios\ContextoNegocioActual;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    Route::get('/_prueba-resolutor/{negocio:slug}', fn (Negocio $negocio): string => (string) $negocio->id)
        ->middleware(['web', ResolverNegocioDesdeRuta::class]);
});

test('un dueno entra a su propio negocio y queda fijado en el contexto', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/_prueba-resolutor/{$negocio->slug}");

    $respuesta->assertOk()->assertSee((string) $negocio->id);
    expect(app(ContextoNegocioActual::class)->actual()?->id)->toBe($negocio->id);
    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBe($negocio->id);
});

test('un dueno no puede entrar al negocio de otro', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $duenoA = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    $respuesta = $this->actingAs($duenoA)->get("/_prueba-resolutor/{$negocioB->slug}");

    $respuesta->assertNotFound();
});

test('sin usuario autenticado no se puede resolver el negocio', function (): void {
    $negocio = Negocio::factory()->create();

    $respuesta = $this->get("/_prueba-resolutor/{$negocio->slug}");

    $respuesta->assertNotFound();
});

test('un negocio suspendido bloquea el acceso con 403', function (): void {
    $negocio = Negocio::factory()->suspendido()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    $respuesta = $this->actingAs($dueno)->get("/_prueba-resolutor/{$negocio->slug}");

    $respuesta->assertForbidden();
});

test('un super admin suplantando a un negocio suspendido si puede entrar', function (): void {
    $negocio = Negocio::factory()->suspendido()->create();
    $superAdmin = Usuario::factory()->create(['es_super_admin' => true]);

    $respuesta = $this->actingAs($superAdmin)
        ->withSession(['negocio_suplantado_id' => $negocio->id])
        ->get("/_prueba-resolutor/{$negocio->slug}");

    $respuesta->assertOk();
});
