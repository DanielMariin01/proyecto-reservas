<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\ServicioDeCorreoServiceProvider;

return [
    AppServiceProvider::class,
    ServicioDeCorreoServiceProvider::class,
    AdminPanelProvider::class,
    AppPanelProvider::class,
];
