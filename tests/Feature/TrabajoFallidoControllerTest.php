<?php

declare(strict_types=1);

use App\Models\Usuario;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function crearTrabajoFallido(string $nombreJob = 'App\\Jobs\\TrabajoDeEjemplo', ?CarbonInterface $falloEn = null): array
{
    $uuid = (string) Str::uuid();

    $id = DB::table('failed_jobs')->insertGetId([
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => $nombreJob]),
        'exception' => 'RuntimeException: algo falló en la cola',
        'failed_at' => $falloEn ?? now(),
    ]);

    return ['id' => $id, 'uuid' => $uuid];
}

test('un usuario normal no puede ver la pagina de trabajos fallidos', function (): void {
    $usuario = Usuario::factory()->create();

    $respuesta = $this->actingAs($usuario)->get('/admin/trabajos-fallidos');

    $respuesta->assertForbidden();
});

test('el super admin ve la pagina de trabajos fallidos', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $respuesta = $this->actingAs($superAdmin)->get('/admin/trabajos-fallidos');

    $respuesta->assertOk()->assertSee('Trabajos fallidos');
});

test('la api distingue un correo encolado de otro tipo de trabajo', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    crearTrabajoFallido('Illuminate\\Notifications\\SendQueuedNotifications', now()->subMinute());
    crearTrabajoFallido('App\\Jobs\\OtroTrabajo', now());

    $respuesta = $this->actingAs($superAdmin)->getJson('/admin/api/trabajos-fallidos');

    $respuesta->assertOk();
    $datos = $respuesta->json('data');

    expect($datos[0]['tipo'])->toBe('Otro');
    expect($datos[1]['tipo'])->toBe('Correo');
});

test('reintentar vuelve a encolar el trabajo y lo quita de fallidos', function (): void {
    $superAdmin = Usuario::factory()->superAdmin()->create();

    $trabajo = crearTrabajoFallido();

    $respuesta = $this->actingAs($superAdmin)->postJson("/admin/api/trabajos-fallidos/{$trabajo['id']}/reintentar");

    $respuesta->assertOk();
    expect(DB::table('failed_jobs')->where('id', $trabajo['id'])->exists())->toBeFalse();
    expect(DB::table('jobs')->count())->toBe(1);
});
