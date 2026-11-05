<?php

declare(strict_types=1);

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
