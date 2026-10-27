<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permiso;
use App\Enums\RolNegocio;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolesYPermisosSeeder extends Seeder
{
    /**
     * Crea los permisos y los roles base (dueno, recepcion, profesional),
     * compartidos por todos los negocios (negocio_id nulo en la tabla roles),
     * con los permisos que la sección 3 de requerimientos.md asigna a cada uno.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permiso::cases() as $permiso) {
            Permission::findOrCreate($permiso->value, 'web');
        }

        foreach (RolNegocio::cases() as $rolNegocio) {
            $rol = Role::findOrCreate($rolNegocio->value, 'web');

            $rol->syncPermissions(array_map(
                fn (Permiso $permiso): string => $permiso->value,
                $rolNegocio->permisos(),
            ));
        }
    }
}
