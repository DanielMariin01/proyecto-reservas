<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Bloqueo;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Bloqueo
 */
final class RecursoBloqueo extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $zonaHoraria = Negocio::actual()->zona_horaria;
        $inicio = $this->inicio->setTimezone($zonaHoraria);
        $fin = $this->fin->setTimezone($zonaHoraria);

        return [
            'id' => $this->id,
            'tipo' => $this->tipo->value,
            'tipoEtiqueta' => $this->tipo->etiqueta(),
            'fechaInicio' => $inicio->toDateString(),
            'horaInicio' => $inicio->format('H:i'),
            'fechaFin' => $fin->toDateString(),
            'horaFin' => $fin->format('H:i'),
            'motivo' => $this->motivo,
        ];
    }
}
