<?php

declare(strict_types=1);

namespace App\Providers;

use App\Disponibilidad\CadenaReglasFranja;
use App\Disponibilidad\ReglaAvisoMinimo;
use App\Disponibilidad\ReglaCabeEnBloque;
use App\Disponibilidad\ReglaVentanaMaxima;
use App\Models\CategoriaServicio;
use App\Models\Cita;
use App\Models\Gasto;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\PagoSuscripcion;
use App\Models\Profesional;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Negocios\ContextoNegocioActual;
use App\Observabilidad\ContextoObservabilidad;
use App\Policies\PoliticaCategoriaServicio;
use App\Policies\PoliticaCita;
use App\Policies\PoliticaGasto;
use App\Policies\PoliticaNegocio;
use App\Policies\PoliticaPago;
use App\Policies\PoliticaPagoSuscripcion;
use App\Policies\PoliticaProfesional;
use App\Policies\PoliticaResena;
use App\Policies\PoliticaServicio;
use App\Policies\PoliticaUsuario;
use App\Resenas\CadenaReglasResena;
use App\Resenas\ReglaCitaCompletada;
use App\Resenas\ReglaDentroDeVentanaDeResena;
use App\Resenas\ReglaSinResenaPrevia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
    }
}
