<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Negocio;
use App\Models\PagoSuscripcion;
use App\Models\Usuario;
use App\Policies\PoliticaNegocio;
use App\Policies\PoliticaPagoSuscripcion;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Negocio::class, PoliticaNegocio::class);
        Gate::policy(Usuario::class, PoliticaUsuario::class);
        Gate::policy(PagoSuscripcion::class, PoliticaPagoSuscripcion::class);
    }
}
