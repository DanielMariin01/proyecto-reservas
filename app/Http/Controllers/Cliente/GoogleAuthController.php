<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Services\ServicioCuentaCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as RedirectResponseSymfony;
use Throwable;

final class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly ServicioCuentaCliente $servicioCuentaCliente,
    ) {}

    public function redirigir(): RedirectResponseSymfony
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $usuarioGoogle = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('mi-cuenta', ['error' => 'google']);
        }

        $cuenta = $this->servicioCuentaCliente->resolverOCrearDesdeGoogle($usuarioGoogle);

        Auth::guard('cliente')->login($cuenta);

        return redirect()->route('mi-cuenta');
    }
}
