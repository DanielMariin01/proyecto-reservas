<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Resena;
use Illuminate\Foundation\Events\Dispatchable;

final class ResenaCreada implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Resena $resena,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'resena.creada',
            negocio: Negocio::find($this->resena->negocio_id),
            modelo: Resena::class,
            modeloId: $this->resena->id,
            valoresDespues: [
                'calificacion' => $this->resena->calificacion,
                'profesional_id' => $this->resena->profesional_id,
            ],
        );
    }
}
