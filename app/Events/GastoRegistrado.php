<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Gasto;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class GastoRegistrado implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Gasto $gasto,
        public readonly ?Usuario $registradoPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'gasto.registrado',
            negocio: Negocio::find($this->gasto->negocio_id),
            usuario: $this->registradoPor,
            modelo: Gasto::class,
            modeloId: $this->gasto->id,
            valoresDespues: [
                'monto' => $this->gasto->monto,
                'descripcion' => $this->gasto->descripcion,
                'fecha' => $this->gasto->fecha->toDateString(),
            ],
        );
    }
}
