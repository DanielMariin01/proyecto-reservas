<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\ContrasenaRestablecida;
use App\Models\Usuario;
use Illuminate\Support\Facades\Password;

final class RestablecerContrasenaUsuario
{
    public function ejecutar(Usuario $usuarioAfectado, ?Usuario $restablecidoPor = null): void
    {
        Password::sendResetLink(['email' => $usuarioAfectado->email]);

        ContrasenaRestablecida::dispatch($usuarioAfectado, $restablecidoPor);
    }
}
