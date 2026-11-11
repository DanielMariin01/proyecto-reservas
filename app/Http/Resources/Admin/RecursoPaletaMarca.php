<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\PaletaMarca;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaletaMarca
 */
final class RecursoPaletaMarca extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'colorPrimario' => $this->color_primario,
            'colorSecundario' => $this->color_secundario,
            'negociosCount' => $this->negocios_count ?? 0,
        ];
    }
}
