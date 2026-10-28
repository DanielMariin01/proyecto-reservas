<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Servicio
 */
final class RecursoServicioPublico extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'categoria' => $this->categoriaServicio?->nombre,
            'precio' => $this->precio,
            'duracionMinutos' => $this->duracion_minutos,
            'foto' => $this->foto !== null ? Storage::disk('public')->url($this->foto) : null,
        ];
    }
}
