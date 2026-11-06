<?php

declare(strict_types=1);

use App\Http\Controllers\Cliente\AreaClienteController;
use App\Http\Controllers\Cliente\CitasClienteController;
use App\Http\Controllers\Cliente\GoogleAuthController;
use App\Http\Controllers\Cliente\SesionClienteController;
use App\Http\Controllers\ElegirNegocioController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\GestionarCitaVistaController;
use App\Http\Controllers\PaginaNegocioController;
use App\Http\Controllers\RestablecimientoContrasenaController;
use App\Http\Controllers\SesionController;
use App\Http\Controllers\SuplantacionController;
use App\Http\Middleware\VerificarNegocioAceptaReservas;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::get('/ingresar', [SesionController::class, 'mostrar'])->name('login');
Route::post('/ingresar', [SesionController::class, 'iniciar']);

// Los paneles de Filament no registran su propio formulario de login (RF-DOM-02):
// cualquiera que llegue a estas rutas se redirige a la única pantalla de acceso.
Route::redirect('/admin/login', '/ingresar');
Route::redirect('/app/login', '/ingresar');

Route::get('/restablecer-contrasena/{token}', [RestablecimientoContrasenaController::class, 'mostrar'])->name('password.reset');
Route::post('/restablecer-contrasena', [RestablecimientoContrasenaController::class, 'actualizar'])->name('password.update');

// RNF-OBS-05: pública, sin autenticación — la pega un monitor externo. Va
// antes del comodín de un solo segmento de abajo, igual que las demás
// rutas literales de este archivo.
Route::get('/estado', [EstadoController::class, 'mostrar'])->name('estado');

Route::middleware('auth')->group(function (): void {
    Route::get('/elegir-negocio', [ElegirNegocioController::class, 'mostrar'])->name('elegir-negocio');
    Route::get('/elegir-negocio/{negocio}', [ElegirNegocioController::class, 'elegir'])->name('elegir-negocio.entrar');
    Route::get('/suplantacion/salir', [SuplantacionController::class, 'salir'])->name('suplantacion.salir');
});

// CU-02: la pantalla que abre el enlace del correo para ver, cancelar o
// reprogramar una cita. Va antes del comodín de un solo segmento de abajo.
Route::get('/{negocio:slug}/reservas/{token}', [GestionarCitaVistaController::class, 'mostrar'])
    ->name('gestionar-cita');

// T11: panel de cuenta del cliente. Es global (cruza negocios), por eso no
// vive bajo ningún {negocio:slug}; también va antes del comodín de abajo.
Route::get('/mi-cuenta', [AreaClienteController::class, 'mostrar'])->name('mi-cuenta');

Route::get('/mi-cuenta/google/redirect', [GoogleAuthController::class, 'redirigir'])->name('mi-cuenta.google.redirect');
Route::get('/mi-cuenta/google/callback', [GoogleAuthController::class, 'callback'])->name('mi-cuenta.google.callback');

Route::prefix('mi-cuenta/api')->group(function (): void {
    Route::get('/yo', [SesionClienteController::class, 'mostrar']);

    Route::middleware('auth:cliente')->group(function (): void {
        Route::post('/salir', [SesionClienteController::class, 'salir']);
        Route::get('/citas', [CitasClienteController::class, 'index']);
        Route::post('/citas/{cita}/cancelar', [CitasClienteController::class, 'cancelar']);
        Route::post('/citas/{cita}/reprogramar', [CitasClienteController::class, 'reprogramar']);
        Route::post('/citas/{cita}/resena', [CitasClienteController::class, 'resena']);
    });
});

// RF-DOM-01: la página pública de cada negocio. Debe quedar al final del
// archivo: es un comodín de un solo segmento que, si se registrara antes,
// atraparía rutas como /ingresar o /elegir-negocio.
Route::get('/{negocio:slug}', [PaginaNegocioController::class, 'mostrar'])
    ->middleware(VerificarNegocioAceptaReservas::class)
    ->name('pagina-negocio');
