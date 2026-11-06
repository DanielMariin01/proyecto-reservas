<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\CuentaClienteCreada;
use App\Models\Cliente;
use App\Models\CuentaCliente;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as UsuarioSocialite;

/**
 * Único punto de entrada de una cuenta de cliente (T11): no hay registro
 * manual, todo pasa por Google. Google ya verificó el correo antes de
 * dejar llegar aquí, así que vincular por correo los Cliente que ya
 * existían en cualquier negocio es seguro sin pedir nada adicional.
 */
final class ServicioCuentaCliente
{
    public function resolverOCrearDesdeGoogle(UsuarioSocialite $usuarioGoogle): CuentaCliente
    {
        $googleId = (string) $usuarioGoogle->getId();
        $correo = $usuarioGoogle->getEmail();

        if ($correo === null) {
            throw ValidationException::withMessages([
                'correo' => 'No pudimos obtener tu correo de Google.',
            ]);
        }

        $cuenta = CuentaCliente::query()->where('google_id', $googleId)->first();

        if ($cuenta !== null) {
            return $cuenta;
        }

        $cuenta = CuentaCliente::query()->where('correo', $correo)->first();

        if ($cuenta !== null) {
            $cuenta->update(['google_id' => $googleId]);

            return $cuenta;
        }

        $cuenta = CuentaCliente::create([
            'nombre' => $usuarioGoogle->getName() ?? $correo,
            'correo' => $correo,
            'google_id' => $googleId,
        ]);

        CuentaClienteCreada::dispatch($cuenta);
        $this->vincularClientesExistentes($cuenta);

        return $cuenta;
    }

    public function vincularClientesExistentes(CuentaCliente $cuenta): void
    {
        Cliente::query()
            ->where('correo', $cuenta->correo)
            ->whereNull('cuenta_cliente_id')
            ->update(['cuenta_cliente_id' => $cuenta->id]);
    }
}
