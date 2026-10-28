<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Events\Dispatchable;

final class CitaReprogramada implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Cita $cita,
        public readonly CarbonImmutable $inicioAnterior,
        public readonly ?Usuario $reprogramadaPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'cita.reprogramada',
            negocio: Negocio::find($this->cita->negocio_id),
            usuario: $this->reprogramadaPor,
            modelo: Cita::class,
            modeloId: $this->cita->id,
            valoresAntes: ['inicio' => $this->inicioAnterior->toIso8601String()],
            valoresDespues: ['inicio' => $this->cita->inicio->toIso8601String()],
        );
    }
}
