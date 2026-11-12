<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirigeANegocio;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class SesionController extends Controller
{
    use RedirigeANegocio;

    public function mostrar(): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::guard('web')->user();

        if ($usuario !== null) {
            return $this->redirigirSegunUsuario($usuario);
        }

        return response()->view('auth.ingresar');
    }

    public function iniciar(Request $request): Response
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->attempt($credenciales, $request->boolean('recordarme'))) {
            throw ValidationException::withMessages([
                'email' => 'Esas credenciales no coinciden con ninguna cuenta.',
            ]);
        }

        $request->session()->regenerate();

        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        return $this->redirigirSegunUsuario($usuario);
    }

    public function salir(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirigirSegunUsuario(Usuario $usuario): Response
    {
        if ($usuario->es_super_admin) {
            return redirect()->route('admin.panel');
        }

        $negocios = $usuario->negociosDondeTieneRol();

        if ($negocios->isEmpty()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Tu usuario no está vinculado a ningún negocio todavía. Contacta al administrador.',
            ]);
        }

        if ($negocios->count() > 1) {
            return redirect()->route('elegir-negocio');
        }

        /** @var Negocio $negocio */
        $negocio = $negocios->first();

        return $this->redirigirANegocio($negocio);
    }
}
