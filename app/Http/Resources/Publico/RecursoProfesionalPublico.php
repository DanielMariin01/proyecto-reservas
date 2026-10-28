<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Profesional
 */
final class RecursoProfesionalPublico extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'especialidad' => $this->especialidad,
            'biografia' => $this->biografia,
            'color' => $this->color,
            'foto' => $this->foto !== null ? Storage::disk('public')->url($this->foto) : null,
            'servicios' => $this->servicios->pluck('id'),
        ];
    }
}
