<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;

interface EventoAuditable
{
    public function datosAuditoria(): DatosAuditoria;
}
