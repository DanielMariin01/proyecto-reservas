<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Negocio;
use App\Negocios\ContextoNegocioActual;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

final class EstablecerNegocioActualComoEquipoDePermisos
{
    public function __construct(
        private readonly PermissionRegistrar $registroDePermisos,
        private readonly ContextoNegocioActual $contextoNegocioActual,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        $negocio = $tenant instanceof Negocio ? $tenant : null;

        $this->registroDePermisos->setPermissionsTeamId($negocio?->id);
        $this->contextoNegocioActual->fijar($negocio);

        return $next($request);
    }
}
