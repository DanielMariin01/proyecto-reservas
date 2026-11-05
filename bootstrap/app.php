<?php

declare(strict_types=1);

use App\Actions\RegistrarErrorSistema;
use App\Exceptions\ManejadorRespuestaDeError;
use App\Http\Middleware\AsignarIdPeticion;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(AsignarIdPeticion::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * RNF-OBS-03: guarda cada excepción reportada (negocio_id/usuario_id/
         * id_peticion vía ContextoObservabilidad) para el panel de salud.
         * No se excluye el modo debug aquí: en local también interesa ver
         * qué cayó en la tabla al probar. Los errores de validación sí se
         * excluyen — un 422 de un formulario no es un "error del sistema".
         */
        $exceptions->report(function (Throwable $excepcion) {
            if ($excepcion instanceof ValidationException) {
                return;
            }

            app(RegistrarErrorSistema::class)->ejecutar($excepcion, request());
        });

        /**
         * RNF-OBS-07: mensaje en lenguaje simple + código de referencia.
         * Se deja pasar a la gestión normal de Laravel en modo debug (para
         * seguir viendo la traza completa en local) y los errores de
         * validación (su forma con `errors` por campo la usan los
         * formularios de la página pública).
         */
        $exceptions->render(function (Throwable $excepcion, Request $peticion) {
            if (config('app.debug') || $excepcion instanceof ValidationException) {
                return null;
            }

            return app(ManejadorRespuestaDeError::class)->responder($excepcion, $peticion);
        });
    })->create();
