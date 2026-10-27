<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;
use App\Models\Usuario;

final readonly class DatosCambioEstadoNegocio
{
    public function __construct(
        public Negocio $negocio,
        public EstadoSuscripcion $estadoDestino,
        public ?string $motivo = null,
        public ?Usuario $cambiadoPor = null,
    ) {}

    public function esCambioManual(): bool
    {
        return $this->cambiadoPor !== null;
    }
}
