<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class NegocioCreado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Negocio $negocio,
        public readonly Usuario $dueno,
        public readonly ?Usuario $creadoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'negocio.creado',
            negocio: $this->negocio,
            usuario: $this->creadoPor,
            modelo: Negocio::class,
            modeloId: $this->negocio->id,
            valoresDespues: $this->negocio->only(['nombre', 'slug', 'tipo']),
        );
    }
}
