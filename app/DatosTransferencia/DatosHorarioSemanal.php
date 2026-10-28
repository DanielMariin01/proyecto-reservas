<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Profesional;

final readonly class DatosHorarioSemanal
{
    /**
     * @param  array<int, list<array{hora_inicio: string, hora_fin: string}>>  $rangosPorDia  Lista de rangos por cada App\Enums\DiaSemana::value; un día ausente no tiene horario.
     */
    public function __construct(
        public Profesional $profesional,
        public array $rangosPorDia,
    ) {}
}
