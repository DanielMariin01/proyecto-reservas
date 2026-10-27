<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosInvitacionUsuario;
use App\Events\UsuarioInvitado;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

final class InvitarUsuarioANegocio
{
    public function __construct(
        private readonly PermissionRegistrar $registroDePermisos,
    ) {}

    public function ejecutar(DatosInvitacionUsuario $datos): Usuario
    {
        $usuario = Usuario::firstOrCreate(
            ['email' => $datos->email],
            ['nombre' => $datos->nombre, 'password' => Hash::make(Str::random(32))],
        );

        $this->registroDePermisos->setPermissionsTeamId($datos->negocio->id);

        if (! $usuario->hasRole($datos->rol->value)) {
            $usuario->assignRole($datos->rol->value);
        }

        Password::sendResetLink(['email' => $usuario->email]);

        UsuarioInvitado::dispatch($datos->negocio, $usuario, $datos->rol->value, $datos->invitadoPor);

        return $usuario;
    }
}
