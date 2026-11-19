<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

final class SolicitarRestablecimientoContrasenaController extends Controller
{
    public function mostrar(): View
    {
        return view('auth.solicitar-restablecimiento');
    }

    /**
     * Siempre responde con el mismo mensaje, exista o no una cuenta con
     * ese correo: decirle a un visitante anonimo cual de las dos cosas
     * pasó es una fuga de informacion (permite enumerar correos
     * registrados), asi que Password::INVALID_USER no se distingue aqui
     * de RESET_LINK_SENT.
     */
    public function enviar(Request $request): RedirectResponse
    {
        $datos = $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($datos);

        return back()->with('status', __('passwords.sent'));
    }
}
