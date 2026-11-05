<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Resena;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class ResenaRespondida implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Resena $resena,
        public readonly Usuario $respondidoPor,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'resena.respondida',
            negocio: Negocio::find($this->resena->negocio_id),
            usuario: $this->respondidoPor,
            modelo: Resena::class,
            modeloId: $this->resena->id,
            valoresDespues: ['respuesta_texto' => $this->resena->respuesta_texto],
        );
    }
}
