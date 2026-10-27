<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EventoAuditable;
use App\Services\ServicioAuditoria;

final class RegistrarAuditoria
{
    public function __construct(
        private readonly ServicioAuditoria $servicioAuditoria,
    ) {}

    public function handle(EventoAuditable $evento): void
    {
        $this->servicioAuditoria->registrar($evento->datosAuditoria());
    }
}
