<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class HistoriaClinicaActualizada implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly HistoriaClinica $historiaClinica,
        public readonly ?Usuario $actualizadaPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'historia_clinica.actualizada',
            negocio: Negocio::find($this->historiaClinica->negocio_id),
            usuario: $this->actualizadaPor,
            modelo: HistoriaClinica::class,
            modeloId: $this->historiaClinica->id,
            valoresDespues: [
                'cliente_id' => $this->historiaClinica->cliente_id,
                'profesional_id' => $this->historiaClinica->profesional_id,
                'fecha' => $this->historiaClinica->fecha->toDateString(),
            ],
        );
    }
}
