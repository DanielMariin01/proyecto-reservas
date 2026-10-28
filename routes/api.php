<?php

declare(strict_types=1);

use App\Http\Controllers\Publico\DisponibilidadController;
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
