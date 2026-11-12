<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\HorarioTrabajo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HorarioTrabajo
 */
final class RecursoHorarioTrabajo extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'diaSemana' => $this->dia_semana->value,
            'diaSemanaEtiqueta' => $this->dia_semana->etiqueta(),
            'horaInicio' => mb_substr((string) $this->hora_inicio, 0, 5),
            'horaFin' => mb_substr((string) $this->hora_fin, 0, 5),
        ];
    }
}
