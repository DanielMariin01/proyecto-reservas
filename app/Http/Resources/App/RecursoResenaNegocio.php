<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Resena
 */
final class RecursoResenaNegocio extends JsonResource
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
            'cliente' => $this->cliente?->nombre,
            'profesional' => $this->profesional?->nombre,
            'calificacion' => $this->calificacion,
            'comentario' => $this->comentario,
            'estado' => $this->estado->value,
            'estadoEtiqueta' => $this->estado->etiqueta(),
            'estaPublicada' => $this->estaPublicada(),
            'respuestaTexto' => $this->respuesta_texto,
        ];
    }
}
