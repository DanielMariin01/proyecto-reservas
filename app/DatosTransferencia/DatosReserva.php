<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Enums\OrigenCita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;

final readonly class DatosReserva
{
    public function __construct(
        public Negocio $negocio,
        public Servicio $servicio,
        public string $nombreCliente,
        public string $celularCliente,
        public ?string $correoCliente,
        public CarbonImmutable $inicio,
        public OrigenCita $origen,
        public ?Profesional $profesional = null,
        public ?string $notas = null,
        public bool $forzar = false,
    ) {}

    public function quiereCualquierProfesional(): bool
    {
        return $this->profesional === null;
    }
}
