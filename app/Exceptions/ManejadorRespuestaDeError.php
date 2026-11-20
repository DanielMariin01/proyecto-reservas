<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Actions\GenerarCodigoDeReferencia;
use App\Observabilidad\ContextoObservabilidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * RNF-OBS-07 / skill diseno-reservas sección 8: construye la respuesta
 * que ve el usuario para cualquier excepción no manejada — mensaje en
 * lenguaje simple y un código de referencia, en JSON para la API
 * pública o una vista genérica para los paneles. Se invoca desde el
 * renderable() de bootstrap/app.php, que ya descarta antes los casos
 * que no deben pasar por aquí (modo debug, errores de validación).
 */
final class ManejadorRespuestaDeError
{
    private const MENSAJE_POR_DEFECTO = 'Algo falló de nuestro lado.';

    /**
     * @var array<int, string>
     */
    private const MENSAJES_POR_ESTADO = [
        404 => 'No encontramos lo que buscabas.',
        403 => 'No tienes permiso para hacer esto.',
        419 => 'Tu sesión expiró, intenta de nuevo.',
        429 => 'Demasiados intentos, espera un momento.',
    ];

    public function __construct(
        private readonly ContextoObservabilidad $contexto,
        private readonly GenerarCodigoDeReferencia $generarCodigo,
    ) {}

    public function responder(Throwable $excepcion, Request $peticion): JsonResponse|Response
    {
        $estado = $excepcion instanceof HttpExceptionInterface ? $excepcion->getStatusCode() : 500;
        $mensaje = self::MENSAJES_POR_ESTADO[$estado] ?? self::MENSAJE_POR_DEFECTO;
        $codigoReferencia = $this->generarCodigo->ejecutar($this->contexto->idPeticion());

        // Las cabeceras propias de la excepción (ej. Retry-After y
        // X-RateLimit-* de un 429 por throttle) no se copian solas al
        // armar una respuesta nueva - sin esto, el frontend no tiene cómo
        // saber cuánto esperar.
        $cabeceras = $excepcion instanceof HttpExceptionInterface ? $excepcion->getHeaders() : [];

        if ($peticion->is('api/*') || $peticion->expectsJson()) {
            return response()->json([
                'mensaje' => "{$mensaje} Código de referencia: {$codigoReferencia}.",
                'codigoReferencia' => $codigoReferencia,
            ], $estado, $cabeceras);
        }

        return response()->view('error-generico', [
            'mensaje' => $mensaje,
            'codigoReferencia' => $codigoReferencia,
        ], $estado, $cabeceras);
    }
}
