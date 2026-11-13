<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\NegocioController;
use App\Http\Controllers\Admin\NegocioOpcionesController;
use App\Http\Controllers\Admin\PaginaNegocioController as PaginaNegocioAdminController;
use App\Http\Controllers\Admin\PaginaPaletaMarcaController;
use App\Http\Controllers\Admin\PaginaRegistroAuditoriaController;
use App\Http\Controllers\Admin\PaginaRegistroErrorController;
use App\Http\Controllers\Admin\PaginaResenaController;
use App\Http\Controllers\Admin\PaginaTrabajoFallidoController;
use App\Http\Controllers\Admin\PaginaUsuarioController;
use App\Http\Controllers\Admin\PaginaWebController;
use App\Http\Controllers\Admin\PagoSuscripcionController;
use App\Http\Controllers\Admin\PaletaMarcaController;
use App\Http\Controllers\Admin\PanelAdminController;
use App\Http\Controllers\Admin\RegistroAuditoriaController;
use App\Http\Controllers\Admin\RegistroErrorController;
use App\Http\Controllers\Admin\ResenaController;
use App\Http\Controllers\Admin\TrabajoFallidoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\App\AgendaDelDiaController;
use App\Http\Controllers\App\BloqueoController;
use App\Http\Controllers\App\CategoriaServicioController;
use App\Http\Controllers\App\CitaAccionesController;
use App\Http\Controllers\App\CitaController;
use App\Http\Controllers\App\MiHorarioController;
use App\Http\Controllers\App\MisCitasHoyController;
use App\Http\Controllers\App\PaginaAgendaDelDiaController;
use App\Http\Controllers\App\PaginaCategoriaServicioController;
use App\Http\Controllers\App\PaginaCitaController;
use App\Http\Controllers\App\PaginaMiHorarioController;
use App\Http\Controllers\App\PaginaMisCitasHoyController;
use App\Http\Controllers\App\PaginaPaginaWebController;
use App\Http\Controllers\App\PaginaPanelDelDiaController;
use App\Http\Controllers\App\PaginaProfesionalController;
use App\Http\Controllers\App\PaginaResenaController as PaginaResenaAppController;
use App\Http\Controllers\App\PaginaServicioController;
use App\Http\Controllers\App\PaginaUsuarioController as PaginaUsuarioAppController;
use App\Http\Controllers\App\PaginaWebController as PaginaWebAppController;
use App\Http\Controllers\App\PanelDelDiaController;
use App\Http\Controllers\App\PanelNegocioController;
use App\Http\Controllers\App\ProfesionalController;
use App\Http\Controllers\App\ProfesionalHorarioController;
use App\Http\Controllers\App\ProfesionalServicioController;
use App\Http\Controllers\App\ResenaController as ResenaAppController;
use App\Http\Controllers\App\ServicioController;
use App\Http\Controllers\App\UsuarioActualController;
use App\Http\Controllers\App\UsuarioController as UsuarioAppController;
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
use App\Http\Middleware\ResolverNegocioDesdeRuta;
use App\Http\Middleware\VerificarNegocioAceptaReservas;
use App\Http\Middleware\VerificarSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::get('/ingresar', [SesionController::class, 'mostrar'])->name('login');
Route::post('/ingresar', [SesionController::class, 'iniciar']);

// RF-DOM-02: una sola pantalla de acceso para ambos paneles; estos paths
// se conservan por compatibilidad con enlaces antiguos de cuando los
// paneles eran de Filament.
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
    Route::post('/salir', [SesionController::class, 'salir'])->name('salir');
});

Route::middleware(['auth', VerificarSuperAdmin::class])->group(function (): void {
    // T12 (migracion de Filament): raiz del panel admin, reemplaza el
    // Dashboard por defecto de Filament. El resto de recursos siguen en
    // /admin/* bajo Filament mientras se migran uno por uno.
    Route::get('/admin', [PanelAdminController::class, 'mostrar'])->name('admin.panel');

    // T15 (migracion de Filament): primeras pantallas migradas (solo
    // lectura), en el mismo path que usaban sus resources de Filament.
    Route::get('/admin/registro-auditorias', [PaginaRegistroAuditoriaController::class, 'mostrar'])->name('admin.registro-auditorias');
    Route::get('/admin/registro-errores', [PaginaRegistroErrorController::class, 'mostrar'])->name('admin.registro-errores');
    Route::get('/admin/trabajos-fallidos', [PaginaTrabajoFallidoController::class, 'mostrar'])->name('admin.trabajos-fallidos');

    // T16 (migracion de Filament): primer CRUD completo migrado.
    Route::get('/admin/paleta-marcas', [PaginaPaletaMarcaController::class, 'index'])->name('admin.paleta-marcas.index');
    Route::get('/admin/paleta-marcas/create', [PaginaPaletaMarcaController::class, 'create'])->name('admin.paleta-marcas.create');
    Route::get('/admin/paleta-marcas/{paleta}/edit', [PaginaPaletaMarcaController::class, 'edit'])->name('admin.paleta-marcas.edit');

    // T17 (migracion de Filament): usuarios super admin.
    Route::get('/admin/usuarios', [PaginaUsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::get('/admin/usuarios/create', [PaginaUsuarioController::class, 'create'])->name('admin.usuarios.create');
    Route::get('/admin/usuarios/{usuario}/edit', [PaginaUsuarioController::class, 'edit'])->name('admin.usuarios.edit');

    // T18 (migracion de Filament): listado de resenas con acciones de visibilidad.
    Route::get('/admin/resenas', [PaginaResenaController::class, 'index'])->name('admin.resenas.index');

    // T19 (migracion de Filament): CRUD basico de negocios. El Builder de
    // bloques de pagina se migra aparte (T20), por eso el formulario de
    // edicion todavia no lo incluye.
    Route::get('/admin/negocios', [PaginaNegocioAdminController::class, 'index'])->name('admin.negocios.index');
    Route::get('/admin/negocios/create', [PaginaNegocioAdminController::class, 'create'])->name('admin.negocios.create');
    Route::get('/admin/negocios/{negocio:slug}', [PaginaNegocioAdminController::class, 'show'])->name('admin.negocios.show');
    Route::get('/admin/negocios/{negocio:slug}/edit', [PaginaNegocioAdminController::class, 'edit'])->name('admin.negocios.edit');

    Route::prefix('admin/api')->group(function (): void {
        Route::get('/registro-auditorias', [RegistroAuditoriaController::class, 'index']);
        Route::get('/registro-errores', [RegistroErrorController::class, 'index']);
        Route::get('/negocios-opciones', [NegocioOpcionesController::class, 'mostrar']);
        Route::get('/trabajos-fallidos', [TrabajoFallidoController::class, 'index']);
        Route::post('/trabajos-fallidos/{trabajo}/reintentar', [TrabajoFallidoController::class, 'reintentar']);

        Route::get('/paleta-marcas', [PaletaMarcaController::class, 'index']);
        Route::post('/paleta-marcas', [PaletaMarcaController::class, 'store']);
        Route::put('/paleta-marcas/{paleta}', [PaletaMarcaController::class, 'update']);
        Route::delete('/paleta-marcas/{paleta}', [PaletaMarcaController::class, 'destroy']);

        Route::get('/usuarios', [UsuarioController::class, 'index']);
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update']);
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy']);
        Route::post('/usuarios/{usuario}/restablecer-contrasena', [UsuarioController::class, 'restablecerContrasena']);

        Route::get('/resenas', [ResenaController::class, 'index']);
        Route::post('/resenas/{resena}/ocultar', [ResenaController::class, 'ocultar']);
        Route::post('/resenas/{resena}/mostrar', [ResenaController::class, 'mostrar']);

        Route::get('/negocios', [NegocioController::class, 'index']);
        Route::get('/negocios/{negocio:slug}', [NegocioController::class, 'show']);
        Route::post('/negocios', [NegocioController::class, 'store']);
        Route::put('/negocios/{negocio:slug}', [NegocioController::class, 'update']);
        Route::delete('/negocios/{negocio:slug}', [NegocioController::class, 'destroy']);
        Route::post('/negocios/{negocio:slug}/entrar', [NegocioController::class, 'entrarComoNegocio']);
        Route::post('/negocios/{negocio:slug}/registrar-pago', [NegocioController::class, 'registrarPago']);
        Route::post('/negocios/{negocio:slug}/cambiar-estado', [NegocioController::class, 'cambiarEstado']);
        Route::get('/negocios/{negocio:slug}/pagos-suscripcion', [PagoSuscripcionController::class, 'index']);

        // T20 (migracion de Filament): Builder de bloques de la pagina web.
        Route::get('/negocios/{negocio:slug}/pagina', [PaginaWebController::class, 'show']);
        Route::put('/negocios/{negocio:slug}/pagina', [PaginaWebController::class, 'update']);
        Route::post('/negocios/{negocio:slug}/pagina/publicar', [PaginaWebController::class, 'publicar']);
        Route::post('/negocios/{negocio:slug}/pagina/imagenes', [PaginaWebController::class, 'subirImagen']);
    });
});

// CU-02: la pantalla que abre el enlace del correo para ver, cancelar o
// reprogramar una cita. Va antes del comodín de un solo segmento de abajo.
Route::get('/{negocio:slug}/reservas/{token}', [GestionarCitaVistaController::class, 'mostrar'])
    ->name('gestionar-cita');

// T14 (migracion de Filament): raiz del panel app, reemplaza el Dashboard
// por defecto de Filament. El resto de recursos siguen en /app/{slug}/*
// bajo Filament mientras se migran uno por uno.
Route::get('/app/{negocio:slug}', [PanelNegocioController::class, 'mostrar'])
    ->middleware(['auth', ResolverNegocioDesdeRuta::class])
    ->name('app.panel');

// T21 (migracion de Filament): primeros resources del panel app migrados
// (catalogo), ya con tenancy real activa via ResolverNegocioDesdeRuta.
// Los {servicio}/{categoriaServicio} de las rutas de abajo son ids simples
// (sin binding implicito de Eloquent): el controlador los resuelve el
// mismo dentro del metodo, ya con el ScopeNegocio activo, para no
// depender del orden relativo entre SubstituteBindings y este middleware.
Route::middleware(['auth', ResolverNegocioDesdeRuta::class])->prefix('app/{negocio:slug}')->group(function (): void {
    Route::get('/servicios', [PaginaServicioController::class, 'index'])->name('app.servicios.index');
    Route::get('/servicios/create', [PaginaServicioController::class, 'create'])->name('app.servicios.create');
    Route::get('/servicios/{servicio}/edit', [PaginaServicioController::class, 'edit'])->name('app.servicios.edit');

    Route::get('/categorias-servicio', [PaginaCategoriaServicioController::class, 'index'])->name('app.categorias-servicio.index');
    Route::get('/categorias-servicio/create', [PaginaCategoriaServicioController::class, 'create'])->name('app.categorias-servicio.create');
    Route::get('/categorias-servicio/{categoriaServicio}/edit', [PaginaCategoriaServicioController::class, 'edit'])
        ->name('app.categorias-servicio.edit');

    // T22 (migracion de Filament): usuarios del negocio (staff).
    Route::get('/usuarios', [PaginaUsuarioAppController::class, 'index'])->name('app.usuarios.index');
    Route::get('/usuarios/create', [PaginaUsuarioAppController::class, 'create'])->name('app.usuarios.create');
    Route::get('/usuarios/{usuario}/edit', [PaginaUsuarioAppController::class, 'edit'])->name('app.usuarios.edit');

    // T23 (migracion de Filament): resenas del negocio (sin crear/editar/eliminar).
    Route::get('/resenas', [PaginaResenaAppController::class, 'index'])->name('app.resenas.index');

    // T24 (migracion de Filament): profesionales, con bloqueos y servicios
    // vinculados.
    Route::get('/profesionales', [PaginaProfesionalController::class, 'index'])->name('app.profesionales.index');
    Route::get('/profesionales/create', [PaginaProfesionalController::class, 'create'])->name('app.profesionales.create');
    Route::get('/profesionales/{profesional}', [PaginaProfesionalController::class, 'show'])->name('app.profesionales.show');
    Route::get('/profesionales/{profesional}/edit', [PaginaProfesionalController::class, 'edit'])->name('app.profesionales.edit');

    // T25 (migracion de Filament): "Mi horario" (CU-03/CU-04), el
    // autoservicio del profesional sobre su propio horario y bloqueos
    // rapidos. El editor de horario semanal y la vista previa de
    // disponibilidad del perfil del profesional (vista del dueno,
    // diferidos en T24) tambien quedan aqui.
    Route::get('/mi-horario', [PaginaMiHorarioController::class, 'mostrar'])->name('app.mi-horario');

    // T26 (migracion de Filament): panel del dia (RF-REP-01), el tablero
    // del dueno con las citas de hoy, ingresos registrados y huecos
    // libres por profesional.
    Route::get('/panel-del-dia', [PaginaPanelDelDiaController::class, 'mostrar'])->name('app.panel-del-dia');

    // T27 (migracion de Filament): agenda visual del dueno (RF-RES-07) y
    // mis citas de hoy, el autoservicio del profesional (CU-04). Las
    // acciones sobre cada cita viven en CitaAccionesController (T28),
    // compartidas con la lista general de citas: nunca duplicar esa logica.
    Route::get('/agenda', [PaginaAgendaDelDiaController::class, 'mostrar'])->name('app.agenda');
    Route::get('/mis-citas-hoy', [PaginaMisCitasHoyController::class, 'mostrar'])->name('app.mis-citas-hoy');

    // T28 (migracion de Filament): lista general de citas (RF-RES-06) y
    // creacion manual de una cita desde el panel.
    Route::get('/citas', [PaginaCitaController::class, 'index'])->name('app.citas.index');
    Route::get('/citas/create', [PaginaCitaController::class, 'create'])->name('app.citas.create');

    // T29 (migracion de Filament): el dueno edita el texto y las fotos de
    // los bloques que el super admin ya armo en el Builder (admin, T20),
    // sin tocar estructura ni colores (RF-PAG-01). Ultimo resource de la
    // migracion Filament -> React en el panel app.
    Route::get('/pagina-web', [PaginaPaginaWebController::class, 'mostrar'])->name('app.pagina-web');

    Route::prefix('api')->group(function (): void {
        Route::get('/usuario-actual', [UsuarioActualController::class, 'mostrar']);

        Route::get('/servicios', [ServicioController::class, 'index']);
        Route::post('/servicios', [ServicioController::class, 'store']);
        Route::put('/servicios/{servicio}', [ServicioController::class, 'update']);
        Route::delete('/servicios/{servicio}', [ServicioController::class, 'destroy']);
        Route::post('/servicios/reordenar', [ServicioController::class, 'reordenar']);
        Route::post('/servicios/imagenes', [ServicioController::class, 'subirImagen']);

        Route::get('/categorias-servicio', [CategoriaServicioController::class, 'index']);
        Route::post('/categorias-servicio', [CategoriaServicioController::class, 'store']);
        Route::put('/categorias-servicio/{categoriaServicio}', [CategoriaServicioController::class, 'update']);
        Route::delete('/categorias-servicio/{categoriaServicio}', [CategoriaServicioController::class, 'destroy']);
        Route::post('/categorias-servicio/reordenar', [CategoriaServicioController::class, 'reordenar']);

        Route::get('/usuarios', [UsuarioAppController::class, 'index']);
        Route::post('/usuarios', [UsuarioAppController::class, 'store']);
        Route::put('/usuarios/{usuario}', [UsuarioAppController::class, 'update']);
        Route::delete('/usuarios/{usuario}', [UsuarioAppController::class, 'destroy']);

        Route::get('/resenas', [ResenaAppController::class, 'index']);
        Route::post('/resenas/{resena}/responder', [ResenaAppController::class, 'responder']);
        Route::post('/resenas/{resena}/ocultar', [ResenaAppController::class, 'ocultar']);
        Route::post('/resenas/{resena}/mostrar', [ResenaAppController::class, 'mostrar']);

        Route::get('/profesionales', [ProfesionalController::class, 'index']);
        Route::post('/profesionales', [ProfesionalController::class, 'store']);
        Route::put('/profesionales/{profesional}', [ProfesionalController::class, 'update']);
        Route::delete('/profesionales/{profesional}', [ProfesionalController::class, 'destroy']);
        Route::post('/profesionales/imagenes', [ProfesionalController::class, 'subirImagen']);
        Route::post('/profesionales/{profesional}/vincular-usuario', [ProfesionalController::class, 'vincularUsuario']);

        Route::get('/profesionales/{profesional}/bloqueos', [BloqueoController::class, 'index']);
        Route::post('/profesionales/{profesional}/bloqueos', [BloqueoController::class, 'store']);
        Route::put('/profesionales/{profesional}/bloqueos/{bloqueo}', [BloqueoController::class, 'update']);
        Route::delete('/profesionales/{profesional}/bloqueos/{bloqueo}', [BloqueoController::class, 'destroy']);

        Route::get('/profesionales/{profesional}/servicios', [ProfesionalServicioController::class, 'index']);
        Route::post('/profesionales/{profesional}/servicios', [ProfesionalServicioController::class, 'store']);
        Route::put('/profesionales/{profesional}/servicios/{servicio}', [ProfesionalServicioController::class, 'update']);
        Route::delete('/profesionales/{profesional}/servicios/{servicio}', [ProfesionalServicioController::class, 'destroy']);

        Route::get('/profesionales/{profesional}/horario', [ProfesionalHorarioController::class, 'mostrar']);
        Route::put('/profesionales/{profesional}/horario', [ProfesionalHorarioController::class, 'actualizar']);
        Route::get('/profesionales/{profesional}/vista-previa-disponibilidad', [ProfesionalHorarioController::class, 'vistaPreviaDisponibilidad']);

        Route::get('/mi-horario', [MiHorarioController::class, 'mostrar']);
        Route::post('/mi-horario/bloqueos', [MiHorarioController::class, 'crearBloqueo']);
        Route::delete('/mi-horario/bloqueos/{bloqueo}', [MiHorarioController::class, 'eliminarBloqueo']);
        Route::get('/mi-horario/vista-previa-disponibilidad', [MiHorarioController::class, 'vistaPreviaDisponibilidad']);

        Route::get('/panel-del-dia', [PanelDelDiaController::class, 'mostrar']);

        Route::get('/agenda', [AgendaDelDiaController::class, 'mostrar']);

        Route::get('/mis-citas-hoy', [MisCitasHoyController::class, 'mostrar']);
        Route::post('/mis-citas-hoy/citas/{cita}/marcar-en-atencion', [MisCitasHoyController::class, 'marcarEnAtencion']);
        Route::post('/mis-citas-hoy/citas/{cita}/marcar-no-asistio', [MisCitasHoyController::class, 'marcarNoAsistio']);
        Route::post('/mis-citas-hoy/citas/{cita}/completar-con-pago', [MisCitasHoyController::class, 'completarConPago']);

        Route::get('/citas', [CitaController::class, 'index']);
        Route::post('/citas', [CitaController::class, 'store']);

        // Compartidas entre la agenda, la lista general de citas y mis
        // citas de hoy (las 3 solo difieren en que muestran y filtran, no
        // en como actuan sobre una cita puntual).
        Route::post('/citas/{cita}/cambiar-estado', [CitaAccionesController::class, 'cambiarEstado']);
        Route::post('/citas/{cita}/completar-con-pago', [CitaAccionesController::class, 'completarConPago']);
        Route::post('/citas/{cita}/cancelar', [CitaAccionesController::class, 'cancelar']);
        Route::post('/citas/{cita}/reprogramar', [CitaAccionesController::class, 'reprogramar']);
        Route::post('/citas/{cita}/anular-pago', [CitaAccionesController::class, 'anularPago']);

        Route::get('/pagina', [PaginaWebAppController::class, 'show']);
        Route::post('/pagina/guardar-y-publicar', [PaginaWebAppController::class, 'guardarYPublicar']);
        Route::post('/pagina/imagenes', [PaginaWebAppController::class, 'subirImagen']);
    });
});

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
