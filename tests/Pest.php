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
 * Sin RefreshDatabase: la prueba de concurrencia real lanza procesos de
 * PHP aparte que necesitan ver los datos de la preparación con su propia
 * conexión a la base de datos, y la envoltura transaccional de
 * RefreshDatabase los ocultaría (nunca se hace commit).
 */
uses(TestCase::class)->in('Concurrencia');

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

/**
 * Un PNG mínimo (solo cabecera IHDR + IEND, sin datos de píxel reales)
 * con el ancho y alto exactos que se le pidan: getimagesize() los lee
 * directo del IHDR sin necesitar una imagen completa ni la extensión GD
 * (no disponible en este entorno), así que basta para probar la regla
 * `dimensions` de Laravel sin generar una imagen real.
 */
function crearPngDePrueba(int $ancho, int $alto): string
{
    $firma = "\x89PNG\x0d\x0a\x1a\x0a";
    $datosIhdr = pack('NNCCCCC', $ancho, $alto, 8, 2, 0, 0, 0);
    $chunkIhdr = pack('N', strlen($datosIhdr)).'IHDR'.$datosIhdr.pack('N', crc32('IHDR'.$datosIhdr));
    $chunkIend = pack('N', 0).'IEND'.pack('N', crc32('IEND'));

    return $firma.$chunkIhdr.$chunkIend;
}
