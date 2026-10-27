<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(function (): void {
        $this->seed(RolesYPermisosSeeder::class);
    })
    ->in('Feature');

/**
 * Crea un negocio y un usuario con el rol dado, sin pasar por CrearNegocio
 * (evita enviar correos de restablecimiento en pruebas que no son sobre eso).
 */
function crearUsuarioConRol(Negocio $negocio, RolNegocio $rol, array $atributos = []): Usuario
{
    $usuario = Usuario::factory()->create($atributos);

    app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);
    $usuario->assignRole($rol->value);

    return $usuario;
}
