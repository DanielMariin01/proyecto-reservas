<?php

declare(strict_types=1);

use App\Http\Controllers\Publico\DisponibilidadController;
use App\Http\Controllers\Publico\GestionarCitaPorTokenController;
use App\Http\Controllers\Publico\PaginaController;
use App\Http\Controllers\Publico\ProfesionalesController;
use App\Http\Controllers\Publico\ReservasController;
use App\Http\Controllers\Publico\ServiciosController;
use App\Http\Middleware\VerificarNegocioAceptaReservas;
use Illuminate\Support\Facades\Route;

// Sección 15.1: la página pública nunca calcula horas ni guarda citas,
// solo consume esta API. Sin autenticación (RF-RES-02): la única puerta
// es que el negocio exista y esté operativo.
Route::prefix('publico/{negocio:slug}')
    ->middleware(VerificarNegocioAceptaReservas::class)
    ->group(function (): void {
        Route::get('/pagina', [PaginaController::class, 'mostrar'])
            ->middleware('throttle:60,1');

        Route::get('/servicios', [ServiciosController::class, 'index'])
            ->middleware('throttle:60,1');

        Route::get('/profesionales', [ProfesionalesController::class, 'index'])
            ->middleware('throttle:60,1');

        Route::get('/disponibilidad', [DisponibilidadController::class, 'index'])
            ->middleware('throttle:60,1');

        // RNF-04: límite de intentos más estricto en el endpoint de reserva.
        Route::post('/reservas', [ReservasController::class, 'store'])
            ->middleware('throttle:5,1');
    });

// CU-02: gestionar una cita ya existente desde el enlace del correo. Va
// fuera del grupo anterior porque debe seguir funcionando aunque el
// negocio quede suspendido mientras tanto (es una cita que ya existe, no
// una reserva nueva).
Route::prefix('publico/{negocio:slug}')
    ->middleware('throttle:20,1')
    ->group(function (): void {
        Route::get('/reservas/{token}', [GestionarCitaPorTokenController::class, 'mostrar']);
        Route::post('/reservas/{token}', [GestionarCitaPorTokenController::class, 'gestionar']);
    });
