<?php

declare(strict_types=1);

use App\Enums\Permiso;
use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('un dueno tiene permiso para gestionar usuarios de su negocio', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);

    expect($dueno->can(Permiso::GestionarUsuarios->value))->toBeTrue();
});

test('un profesional no tiene permiso para gestionar usuarios', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);

    expect($profesional->can(Permiso::GestionarUsuarios->value))->toBeFalse();
});

test('un profesional solo tiene permiso sobre sus propias citas', function (): void {
    $negocio = Negocio::factory()->create();
    $profesional = crearUsuarioConRol($negocio, RolNegocio::Profesional);

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);

    expect($profesional->can(Permiso::GestionarCitasPropias->value))->toBeTrue();
    expect($profesional->can(Permiso::GestionarCitasTodas->value))->toBeFalse();
});

test('un dueno tiene permiso para gestionar resenas', function (): void {
    $negocio = Negocio::factory()->create();
    $dueno = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);

    expect($dueno->can(Permiso::GestionarResenas->value))->toBeTrue();
});

test('la migracion que sincroniza gestionar_resenas en el rol dueno repara un rol desactualizado', function (): void {
    $rolDueno = Role::where('name', RolNegocio::Dueno->value)->where('guard_name', 'web')->firstOrFail();
    $rolDueno->revokePermissionTo(Permiso::GestionarResenas->value);

    expect($rolDueno->hasPermissionTo(Permiso::GestionarResenas->value))->toBeFalse();

    $migracion = require base_path('database/migrations/2026_11_13_001542_sincronizar_permiso_resenas_en_rol_dueno.php');
    $migracion->up();

    expect($rolDueno->refresh()->hasPermissionTo(Permiso::GestionarResenas->value))->toBeTrue();

    $migracion->down();

    expect($rolDueno->refresh()->hasPermissionTo(Permiso::GestionarResenas->value))->toBeFalse();
});

test('un rol de un negocio no da permisos en otro negocio del mismo usuario', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();

    $usuario = crearUsuarioConRol($negocioA, RolNegocio::Dueno);

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocioA->id);
    expect(Usuario::find($usuario->id)->can(Permiso::GestionarUsuarios->value))->toBeTrue();

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocioB->id);
    expect(Usuario::find($usuario->id)->can(Permiso::GestionarUsuarios->value))->toBeFalse();
});
