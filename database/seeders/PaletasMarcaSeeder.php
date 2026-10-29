<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaletaMarca;
use Illuminate\Database\Seeder;

/**
 * Paletas iniciales de la libreria (ver CalculadoraContraste para el
 * contraste exacto de cada color_primario con texto blanco):
 * - Color Barbería: 15,1:1
 * - Color Uñas: 5,9:1
 */
final class PaletasMarcaSeeder extends Seeder
{
    public function run(): void
    {
        PaletaMarca::query()->firstOrCreate(
            ['nombre' => 'Color Barbería'],
            ['color_primario' => '#1B263B', 'color_secundario' => '#415A77'],
        );

        PaletaMarca::query()->firstOrCreate(
            ['nombre' => 'Color Uñas'],
            ['color_primario' => '#C2185B', 'color_secundario' => '#F06292'],
        );
    }
}
