<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contratos\ServicioDeCorreo;
use App\Services\ServicioDeCorreoLaravel;
use Illuminate\Support\ServiceProvider;

final class ServicioDeCorreoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ServicioDeCorreo::class, ServicioDeCorreoLaravel::class);
    }
}
