<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contratos\ServicioFirmaDigital;
use App\Services\ServicioFirmaDigitalDisco;
use Illuminate\Support\ServiceProvider;

final class ServicioFirmaDigitalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ServicioFirmaDigital::class, ServicioFirmaDigitalDisco::class);
    }
}
