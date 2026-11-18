<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class HistoriaClinicaEliminada implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly int $historiaClinicaId,
        public readonly int $negocioId,
        public readonly int $clienteId,
        public readonly int $profesionalId,
        public readonly ?Usuario $eliminadaPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'historia_clinica.eliminada',
            negocio: Negocio::find($this->negocioId),
            usuario: $this->eliminadaPor,
            modelo: 'App\\Models\\HistoriaClinica',
            modeloId: $this->historiaClinicaId,
            valoresAntes: [
                'cliente_id' => $this->clienteId,
                'profesional_id' => $this->profesionalId,
            ],
        );
    }
}
