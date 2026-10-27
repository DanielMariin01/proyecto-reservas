<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Negocio;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

final class EstablecerNegocioActualComoEquipoDePermisos
{
    public function __construct(
        private readonly PermissionRegistrar $registroDePermisos,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        $this->registroDePermisos->setPermissionsTeamId($tenant instanceof Negocio ? $tenant->id : null);

        return $next($request);
    }
}
