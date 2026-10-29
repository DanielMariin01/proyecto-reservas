<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Negocio;
use App\Models\Pagina;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

final class PaginaPublicada implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Pagina $pagina,
        public readonly ?Usuario $publicadaPor = null,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'pagina.publicada',
            negocio: Negocio::find($this->pagina->negocio_id),
            usuario: $this->publicadaPor,
            modelo: Pagina::class,
            modeloId: $this->pagina->id,
        );
    }
}
