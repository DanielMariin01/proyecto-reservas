<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Producto
 */
final class RecursoProducto extends JsonResource
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
            'foto' => $this->foto !== null ? Storage::disk('public')->url($this->foto) : null,
            'precio' => $this->precio,
            'visible' => $this->visible,
            'orden' => $this->orden,
        ];
    }
}
