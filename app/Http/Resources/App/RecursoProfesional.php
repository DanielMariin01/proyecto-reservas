<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Profesional
 */
final class RecursoProfesional extends JsonResource
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
            'foto' => $this->foto !== null ? Storage::disk('public')->url($this->foto) : null,
            'biografia' => $this->biografia,
            'certificaciones' => $this->certificaciones,
            'color' => $this->color,
            'activo' => $this->activo,
            'usuarioEmail' => $this->usuario?->email,
        ];
    }
}
