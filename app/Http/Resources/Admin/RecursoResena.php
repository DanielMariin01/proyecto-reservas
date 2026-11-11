<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Resena
 */
final class RecursoResena extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fecha = $this->created_at ?? now();

        return [
            'id' => $this->id,
            'fecha' => $fecha->format('d/m/Y'),
            'negocio' => $this->negocio?->nombre,
            'cliente' => $this->cliente?->nombre,
            'profesional' => $this->profesional?->nombre,
            'calificacion' => $this->calificacion,
            'comentario' => $this->comentario,
            'estado' => $this->estado->value,
            'estadoEtiqueta' => $this->estado->etiqueta(),
            'estaPublicada' => $this->estaPublicada(),
        ];
    }
}
