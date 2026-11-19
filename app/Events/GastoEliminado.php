<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class GastoEliminado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly int $gastoId,
        public readonly int $negocioId,
        public readonly int $monto,
        public readonly string $descripcion,
        public readonly ?Usuario $eliminadoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'gasto.eliminado',
            negocio: Negocio::find($this->negocioId),
            usuario: $this->eliminadoPor,
            modelo: 'App\\Models\\Gasto',
            modeloId: $this->gastoId,
            valoresAntes: [
                'monto' => $this->monto,
                'descripcion' => $this->descripcion,
            ],
        );
    }
}
