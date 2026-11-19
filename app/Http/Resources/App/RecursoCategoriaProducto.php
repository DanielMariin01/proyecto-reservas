<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\CategoriaProducto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CategoriaProducto
 */
final class RecursoCategoriaProducto extends JsonResource
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
            'productosCount' => $this->productos_count ?? $this->productos()->count(),
        ];
    }
}
