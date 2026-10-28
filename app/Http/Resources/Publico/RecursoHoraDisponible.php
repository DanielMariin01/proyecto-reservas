<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\Disponibilidad\HoraDisponible;
use App\Models\Negocio;
use App\Support\Fecha;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HoraDisponible
 */
final class RecursoHoraDisponible extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Negocio $negocio */
        $negocio = $request->route('negocio');

        return [
            'profesionalId' => $this->profesionalId,
            'inicio' => $this->inicio->toIso8601String(),
            'fin' => $this->fin->toIso8601String(),
            'fechaVisible' => Fecha::formatear($this->inicio, $negocio->zona_horaria),
            'horaVisible' => Hora::formatear($this->inicio, $negocio->zona_horaria),
            'horaFinVisible' => Hora::formatear($this->fin, $negocio->zona_horaria),
        ];
    }
}
