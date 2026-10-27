<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Enums\TipoNegocio;

final readonly class DatosNuevoNegocio
{
    public function __construct(
        public string $nombre,
        public TipoNegocio $tipo,
        public string $nombreDueno,
        public string $emailDueno,
        public ?string $slug = null,
        public ?string $direccion = null,
        public ?string $ciudad = null,
        public ?string $telefono = null,
        public ?string $whatsapp = null,
        public string $zonaHoraria = 'America/Bogota',
    ) {}
}
