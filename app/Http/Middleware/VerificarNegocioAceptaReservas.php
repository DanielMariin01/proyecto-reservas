<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Negocio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RF-SUS-04: un negocio suspendido o cancelado no recibe reservas nuevas,
 * tampoco por API. La página pública solo muestra que no está disponible,
 * sin exponer el motivo (suspensión, pago, etc.) a un visitante.
 */
final class VerificarNegocioAceptaReservas
{
    public function handle(Request $request, Closure $next): Response
    {
        $negocio = $request->route('negocio');

        if (! $negocio instanceof Negocio || $negocio->aceptaReservasPublicas()) {
            return $next($request);
        }

        if ($request->is('api/*')) {
            return response()->json([
                'mensaje' => 'Esta página no está disponible por el momento.',
            ], 404);
        }

        return response()->view('pagina-no-disponible', [], 404);
    }
}
