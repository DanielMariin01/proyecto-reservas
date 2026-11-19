<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Servicio
 */
final class RecursoServicio extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'categoriaServicioId' => $this->categoria_servicio_id,
            'categoriaServicioNombre' => $this->categoriaServicio?->nombre,
            'descripcion' => $this->descripcion,
            'foto' => $this->foto !== null ? Storage::disk('public')->url($this->foto) : null,
            'duracionMinutos' => $this->duracion_minutos,
            'precio' => $this->precio,
            'margenAntesMinutos' => $this->margen_antes_minutos,
            'margenDespuesMinutos' => $this->margen_despues_minutos,
            'intervaloFranjasMinutos' => $this->intervalo_franjas_minutos,
            'visible' => $this->visible,
            'orden' => $this->orden,
        ];
    }
}
