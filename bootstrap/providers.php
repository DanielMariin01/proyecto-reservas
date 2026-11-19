<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\ServicioDeCorreoServiceProvider;
use App\Providers\ServicioFirmaDigitalServiceProvider;

return [
    AppServiceProvider::class,
    ServicioDeCorreoServiceProvider::class,
    ServicioFirmaDigitalServiceProvider::class,
];
