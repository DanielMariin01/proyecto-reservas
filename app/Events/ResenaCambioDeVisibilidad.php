<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Enums\EstadoResenaValor;
use App\Models\Negocio;
use App\Models\Resena;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class ResenaCambioDeVisibilidad implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Resena $resena,
        public readonly EstadoResenaValor $estadoAnterior,
        public readonly Usuario $cambiadoPor,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'resena.cambio_de_visibilidad',
            negocio: Negocio::find($this->resena->negocio_id),
            usuario: $this->cambiadoPor,
            modelo: Resena::class,
            modeloId: $this->resena->id,
            valoresAntes: ['estado' => $this->estadoAnterior->value],
            valoresDespues: ['estado' => $this->resena->estado->value],
        );
    }
}
