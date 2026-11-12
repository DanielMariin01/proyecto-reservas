<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Negocio;
use App\Models\Usuario;
use App\Negocios\ContextoNegocioActual;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Para las rutas del panel `app`: resuelve el negocio desde
 * `{negocio:slug}`, verifica que el usuario autenticado pueda entrar a ese
 * negocio, fija el "team" de permisos y el negocio activo, y bloquea el
 * acceso si el negocio está suspendido o cancelado (RF-SUS, sección 14.5).
 */
final class ResolverNegocioDesdeRuta
{
    public function __construct(
        private readonly PermissionRegistrar $registroDePermisos,
        private readonly ContextoNegocioActual $contextoNegocioActual,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $negocio = $request->route('negocio');

        abort_unless($negocio instanceof Negocio, 404);

        /** @var Usuario|null $usuario */
        $usuario = Auth::guard('web')->user();

        abort_if($usuario === null || ! $usuario->canAccessTenant($negocio), 404);

        $this->registroDePermisos->setPermissionsTeamId($negocio->id);
        $this->contextoNegocioActual->fijar($negocio);

        if (! $negocio->estaOperativo() && ! $usuario->estaSuplantandoA($negocio)) {
            return response()->view('negocio-suspendido', ['negocio' => $negocio], 403);
        }

        return $next($request);
    }
}
