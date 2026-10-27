<?php

declare(strict_types=1);

use App\Actions\CrearProfesional;
use App\DatosTransferencia\DatosNuevoProfesional;
use App\Enums\RolNegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Spatie\Permission\PermissionRegistrar;

test('crear un profesional sin vincular usuario no crea ningun usuario', function (): void {
    $negocio = Negocio::factory()->create();

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(
        negocio: $negocio,
        nombre: 'Ana',
    ));

    expect($profesional->usuario_id)->toBeNull();
    expect(Usuario::count())->toBe(0);
});

test('crear un profesional con datos de usuario lo vincula con rol profesional', function (): void {
    $negocio = Negocio::factory()->create();

    $profesional = app(CrearProfesional::class)->ejecutar(new DatosNuevoProfesional(
        negocio: $negocio,
        nombre: 'Ana',
        nombreUsuario: 'Ana',
        emailUsuario: 'ana@test.com',
    ));

    expect($profesional->usuario_id)->not->toBeNull();

    $usuario = Usuario::find($profesional->usuario_id);
    app(PermissionRegistrar::class)->setPermissionsTeamId($negocio->id);

    expect($usuario->hasRole(RolNegocio::Profesional->value))->toBeTrue();
});
