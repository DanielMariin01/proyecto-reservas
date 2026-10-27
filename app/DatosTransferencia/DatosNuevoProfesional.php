<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Negocio;

final readonly class DatosNuevoProfesional
{
    public function __construct(
        public Negocio $negocio,
        public string $nombre,
        public ?string $foto = null,
        public ?string $especialidad = null,
        public ?string $biografia = null,
        public ?string $certificaciones = null,
        public ?string $color = null,
        public ?string $nombreUsuario = null,
        public ?string $emailUsuario = null,
    ) {}

    public function quiereVincularUsuario(): bool
    {
        return filled($this->nombreUsuario) && filled($this->emailUsuario);
    }
}
