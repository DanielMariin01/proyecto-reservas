<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Negocio;
use App\Models\Usuario;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerificarNegocioOperativo
{
    public function handle(Request $request, Closure $next): Response
    {
        $negocio = Filament::getTenant();

        if (! $negocio instanceof Negocio) {
            return $next($request);
        }

        if ($negocio->estaOperativo()) {
            return $next($request);
        }

        /** @var Usuario|null $usuario */
        $usuario = Filament::auth()->user();

        if ($usuario?->estaSuplantandoA($negocio)) {
            return $next($request);
        }

        return response()->view('negocio-suspendido', ['negocio' => $negocio], 403);
    }
}
