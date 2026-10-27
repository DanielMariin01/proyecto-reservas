<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Negocio;
use App\Models\Usuario;

final readonly class DatosAuditoria
{
    /**
     * @param  array<string, mixed>|null  $valoresAntes
     * @param  array<string, mixed>|null  $valoresDespues
     */
    public function __construct(
        public string $accion,
        public ?Negocio $negocio = null,
        public ?Usuario $usuario = null,
        public ?string $modelo = null,
        public ?int $modeloId = null,
        public ?array $valoresAntes = null,
        public ?array $valoresDespues = null,
        public ?string $motivo = null,
    ) {}
}
