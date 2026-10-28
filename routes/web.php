<?php

declare(strict_types=1);

use App\Http\Controllers\ElegirNegocioController;
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

Route::middleware('auth')->group(function (): void {
    Route::get('/elegir-negocio', [ElegirNegocioController::class, 'mostrar'])->name('elegir-negocio');
    Route::get('/elegir-negocio/{negocio}', [ElegirNegocioController::class, 'elegir'])->name('elegir-negocio.entrar');
    Route::get('/suplantacion/salir', [SuplantacionController::class, 'salir'])->name('suplantacion.salir');
});

// RF-DOM-01: la página pública de cada negocio. Debe quedar al final del
// archivo: es un comodín de un solo segmento que, si se registrara antes,
// atraparía rutas como /ingresar o /elegir-negocio.
Route::get('/{negocio:slug}', [PaginaNegocioController::class, 'mostrar'])
    ->middleware(VerificarNegocioAceptaReservas::class)
    ->name('pagina-negocio');
