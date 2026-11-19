<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Producto
 */
final class RecursoProductoPublico extends JsonResource
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
            'categoria' => $this->categoriaProducto?->nombre,
            'categoriaProductoId' => $this->categoria_producto_id,
            'foto' => $this->foto !== null ? Storage::disk('public')->url($this->foto) : null,
            'precio' => $this->precio,
        ];
    }
}
