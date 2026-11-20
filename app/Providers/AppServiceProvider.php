<?php

declare(strict_types=1);

namespace App\Providers;

use App\Disponibilidad\CadenaReglasFranja;
use App\Disponibilidad\ReglaAvisoMinimo;
use App\Disponibilidad\ReglaCabeEnBloque;
use App\Disponibilidad\ReglaVentanaMaxima;
use App\Models\CategoriaProducto;
use App\Models\CategoriaServicio;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\PagoSuscripcion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Negocios\ContextoNegocioActual;
use App\Observabilidad\ContextoObservabilidad;
use App\Policies\PoliticaCategoriaProducto;
use App\Policies\PoliticaCategoriaServicio;
use App\Policies\PoliticaCita;
use App\Policies\PoliticaCliente;
use App\Policies\PoliticaGasto;
use App\Policies\PoliticaHistoriaClinica;
use App\Policies\PoliticaNegocio;
use App\Policies\PoliticaPago;
use App\Policies\PoliticaPagoSuscripcion;
use App\Policies\PoliticaPedido;
use App\Policies\PoliticaProducto;
use App\Policies\PoliticaProfesional;
use App\Policies\PoliticaResena;
use App\Policies\PoliticaServicio;
use App\Policies\PoliticaUsuario;
use App\Resenas\CadenaReglasResena;
use App\Resenas\ReglaCitaCompletada;
use App\Resenas\ReglaDentroDeVentanaDeResena;
use App\Resenas\ReglaSinResenaPrevia;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * RNF-04: anti-abuso real por cliente, no por IP. Una oficina o wifi
     * compartido reserva desde una sola IP para varias personas; limitar
     * solo por IP las bloquea entre sí. Este número frena a una misma
     * persona (identificada por celular/correo) que insiste demasiado
     * rápido.
     */
    private const INTENTOS_MAXIMOS_POR_CLIENTE_POR_MINUTO = 5;

    /**
     * Techo de respaldo por IP: sigue limitando el caso de abuso real
     * (un script que manda muchas reservas con celulares/correos
     * distintos desde la misma IP), con margen suficiente para varias
     * personas genuinas reservando seguido desde la misma red.
     */
    private const INTENTOS_MAXIMOS_POR_IP_POR_MINUTO = 20;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CadenaReglasFranja::class, fn (): CadenaReglasFranja => new CadenaReglasFranja([
            new ReglaCabeEnBloque,
            new ReglaAvisoMinimo,
            new ReglaVentanaMaxima,
        ]));

        $this->app->singleton(ContextoObservabilidad::class);

        $this->app->singleton(ContextoNegocioActual::class);

        $this->app->singleton(CadenaReglasResena::class, fn (): CadenaReglasResena => new CadenaReglasResena([
            new ReglaCitaCompletada,
            new ReglaSinResenaPrevia,
            new ReglaDentroDeVentanaDeResena,
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Negocio::class, PoliticaNegocio::class);
        Gate::policy(Usuario::class, PoliticaUsuario::class);
        Gate::policy(PagoSuscripcion::class, PoliticaPagoSuscripcion::class);
        Gate::policy(CategoriaServicio::class, PoliticaCategoriaServicio::class);
        Gate::policy(Servicio::class, PoliticaServicio::class);
        Gate::policy(Profesional::class, PoliticaProfesional::class);
        Gate::policy(Cita::class, PoliticaCita::class);
        Gate::policy(Pago::class, PoliticaPago::class);
        Gate::policy(Resena::class, PoliticaResena::class);
        Gate::policy(Gasto::class, PoliticaGasto::class);
        Gate::policy(Producto::class, PoliticaProducto::class);
        Gate::policy(CategoriaProducto::class, PoliticaCategoriaProducto::class);
        Gate::policy(Pedido::class, PoliticaPedido::class);
        Gate::policy(HistoriaClinica::class, PoliticaHistoriaClinica::class);
        Gate::policy(Cliente::class, PoliticaCliente::class);

        /**
         * Equivalente a AsignarIdPeticion pero para trabajos en cola: un
         * worker (php artisan queue:work) es un solo proceso de larga
         * duración que procesa muchos trabajos, así que sin esto todos
         * compartirían el id_peticion que generó perezosamente el primero
         * (ContextoObservabilidad es singleton). Se fija uno nuevo al
         * empezar cada trabajo, igual que la petición web.
         */
        Queue::before(function (JobProcessing $evento): void {
            app(ContextoObservabilidad::class)->definirIdPeticion((string) Str::uuid());
        });

        $this->definirLimitesDeCreacionPublica();
    }

    /**
     * RNF-04: usado por /api/publico/{slug}/reservas y /pedidos. Dos
     * límites independientes - el que se cumpla primero da el 429 (y sus
     * cabeceras Retry-After/X-RateLimit-* reflejan ese límite).
     */
    private function definirLimitesDeCreacionPublica(): void
    {
        RateLimiter::for('creacion-publica', function (Request $peticion): array {
            $identificadorCliente = $peticion->string('celular')->toString()
                ?: $peticion->string('correo')->toString()
                ?: $peticion->ip();

            return [
                Limit::perMinute(self::INTENTOS_MAXIMOS_POR_CLIENTE_POR_MINUTO)->by($identificadorCliente),
                Limit::perMinute(self::INTENTOS_MAXIMOS_POR_IP_POR_MINUTO)->by($peticion->ip()),
            ];
        });
    }
}
