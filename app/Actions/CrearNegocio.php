<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosNuevoNegocio;
use App\Enums\RolNegocio;
use App\Events\NegocioCreado;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

final class CrearNegocio
{
    public function __construct(
        private readonly PermissionRegistrar $registroDePermisos,
    ) {}

    public function ejecutar(DatosNuevoNegocio $datos, ?Usuario $creadoPor = null): Negocio
    {
        [$negocio, $dueno] = DB::transaction(function () use ($datos) {
            $negocio = Negocio::create([
                'nombre' => $datos->nombre,
                'slug' => $this->generarSlugUnico($datos->slug ?? $datos->nombre),
                'tipo' => $datos->tipo,
                'direccion' => $datos->direccion,
                'ciudad' => $datos->ciudad,
                'telefono' => $datos->telefono,
                'whatsapp' => $datos->whatsapp,
                'zona_horaria' => $datos->zonaHoraria,
                'proxima_fecha_pago' => now()->addMonth()->toDateString(),
            ]);

            $dueno = Usuario::create([
                'nombre' => $datos->nombreDueno,
                'email' => $datos->emailDueno,
                'password' => Hash::make(Str::random(32)),
            ]);

            $this->registroDePermisos->setPermissionsTeamId($negocio->id);
            $dueno->assignRole(RolNegocio::Dueno->value);

            return [$negocio, $dueno];
        });

        Password::sendResetLink(['email' => $dueno->email]);

        NegocioCreado::dispatch($negocio, $dueno, $creadoPor);

        return $negocio;
    }

    private function generarSlugUnico(string $nombre): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $sufijo = 1;

        while (Negocio::where('slug', $slug)->exists()) {
            $sufijo++;
            $slug = "{$base}-{$sufijo}";
        }

        return $slug;
    }
}
