<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Observabilidad\ContextoObservabilidad;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Primer middleware global (bootstrap/app.php): genera el id_peticion
 * de la sección 14.6 del CLAUDE.md antes que cualquier otro código corra,
 * para que todo log o error de esta petición pueda incluirlo. También lo
 * manda en la respuesta (X-Id-Peticion) para poder cruzarlo con los logs.
 */
final class AsignarIdPeticion
{
    public function __construct(private readonly ContextoObservabilidad $contexto) {}

    public function handle(Request $request, Closure $next): Response
    {
        $idPeticion = (string) Str::uuid();
        $this->contexto->definirIdPeticion($idPeticion);

        $response = $next($request);
        $response->headers->set('X-Id-Peticion', $idPeticion);

        return $response;
    }
}
