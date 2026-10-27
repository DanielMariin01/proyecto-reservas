<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

final class RestablecimientoContrasenaController extends Controller
{
    public function mostrar(Request $request, string $token): View
    {
        return view('auth.restablecer-contrasena', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $estado = Password::reset(
            $datos,
            function ($usuario, string $password): void {
                $usuario->forceFill(['password' => Hash::make($password)])->save();
            },
        );

        if ($estado !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($estado)]);
        }

        return redirect()->route('login')->with('status', 'Tu contraseña quedó actualizada. Ya puedes ingresar.');
    }
}
