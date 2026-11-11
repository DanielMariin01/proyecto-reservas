<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Servicio
 */
final class RecursoServicioVinculado extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'precioPropio' => $this->pivot?->precio,
            'duracionPropiaMinutos' => $this->pivot?->duracion_minutos,
        ];
    }
}
