<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege las rutas del panel admin: solo el super admin entra, el resto
 * recibe 403 (RF-DOM-02).
 */
final class VerificarSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::guard('web')->user();

        abort_if($usuario === null || ! $usuario->es_super_admin, 403);

        return $next($request);
    }
}
