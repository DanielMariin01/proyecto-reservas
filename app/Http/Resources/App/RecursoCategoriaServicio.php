<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\CategoriaServicio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CategoriaServicio
 */
final class RecursoCategoriaServicio extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'orden' => $this->orden,
            'serviciosCount' => $this->servicios_count ?? $this->servicios()->count(),
        ];
    }
}
