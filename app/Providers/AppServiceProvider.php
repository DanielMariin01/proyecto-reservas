<?php

declare(strict_types=1);

namespace App\Providers;

use App\Disponibilidad\CadenaReglasFranja;
use App\Disponibilidad\ReglaAvisoMinimo;
use App\Disponibilidad\ReglaCabeEnBloque;
use App\Disponibilidad\ReglaVentanaMaxima;
use App\Models\CategoriaServicio;
use App\Models\Negocio;
use App\Models\PagoSuscripcion;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Policies\PoliticaCategoriaServicio;
use App\Policies\PoliticaNegocio;
use App\Policies\PoliticaPagoSuscripcion;
use App\Policies\PoliticaProfesional;
use App\Policies\PoliticaServicio;
use App\Policies\PoliticaUsuario;
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
    }
}
