<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\CrearNegocio;
use App\DatosTransferencia\DatosNuevoNegocio;
use App\Enums\TipoNegocio;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class NegociosDemoSeeder extends Seeder
{
    /**
     * Dos negocios de prueba con su propio dueño, para poder probar a mano
     * el aislamiento entre negocios y la suplantación del super admin.
     *
     * Usa la acción real CrearNegocio (crea el negocio, el dueño y le asigna
     * el rol), pero luego fija la contraseña a 'password' porque la acción
     * genera una aleatoria y envía un enlace de restablecimiento, pensado
     * para producción, no para pruebas manuales locales.
     */
    public function run(): void
    {
        $this->crearNegocioDemo(new DatosNuevoNegocio(
            nombre: 'Barber Japo',
            tipo: TipoNegocio::Barberia,
            nombreDueno: 'Japo',
            emailDueno: 'japo@reservas.test',
            slug: 'barberjapo',
        ));

        $this->crearNegocioDemo(new DatosNuevoNegocio(
            nombre: 'Spa Luna',
            tipo: TipoNegocio::Spa,
            nombreDueno: 'Luna',
            emailDueno: 'luna@reservas.test',
            slug: 'spaluna',
        ));
    }

    private function crearNegocioDemo(DatosNuevoNegocio $datos): void
    {
        app(CrearNegocio::class)->ejecutar($datos);

        Usuario::where('email', $datos->emailDueno)->update([
            'password' => Hash::make('password'),
        ]);
    }
}
