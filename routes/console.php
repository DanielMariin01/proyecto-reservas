<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('suscripciones:actualizar-estados')->dailyAt('06:00');

Schedule::command('citas:enviar-recordatorios')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('citas:enviar-resumen-diario-profesionales')->dailyAt('07:00')->withoutOverlapping();

// Hosting compartido sin procesos permanentes (CLAUDE.md): en vez de un
// `queue:work` que corra indefinidamente, el cron dispara schedule:run
// cada minuto y esto procesa lo que haya en cola hasta por 55 segundos,
// para que el correo de confirmación llegue en menos de 1 minuto (CU-01).
Schedule::command('queue:work --stop-when-empty --max-time=55')->everyMinute()->withoutOverlapping();
