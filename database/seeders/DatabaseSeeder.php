<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesYPermisosSeeder::class);
        $this->call(PaletasMarcaSeeder::class);

        Usuario::factory()->superAdmin()->create([
            'nombre' => 'Super Admin',
            'email' => 'superadmin@reservas.test',
        ]);

        $this->call(NegociosDemoSeeder::class);
    }
}
