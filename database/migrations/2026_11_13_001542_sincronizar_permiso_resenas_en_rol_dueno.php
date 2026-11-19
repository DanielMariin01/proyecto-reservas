<?php

declare(strict_types=1);

use App\Enums\Permiso;
use App\Enums\RolNegocio;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * T10 (Reseñas, 5 nov 2026) agregó Permiso::GestionarResenas al rol dueño
 * en RolNegocio::Dueno->permisos(), pero el rol "dueno" ya existía en
 * cualquier entorno donde RolesYPermisosSeeder ya había corrido (local,
 * producción), y nunca se volvió a sincronizar: el dueño quedaba sin
 * acceso a Reseñas pese a que el código ya se lo otorgaba. El rol es
 * compartido entre negocios (negocio_id nulo), así que hay una sola fila
 * que corregir.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permiso = Permission::findOrCreate(Permiso::GestionarResenas->value, 'web');
        $rolDueno = Role::where('name', RolNegocio::Dueno->value)->where('guard_name', 'web')->first();

        if ($rolDueno !== null && ! $rolDueno->hasPermissionTo($permiso)) {
            $rolDueno->givePermissionTo($permiso);
        }
    }

    public function down(): void
    {
        $rolDueno = Role::where('name', RolNegocio::Dueno->value)->where('guard_name', 'web')->first();
        $rolDueno?->revokePermissionTo(Permiso::GestionarResenas->value);
    }
};
