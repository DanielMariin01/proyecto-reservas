<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\TrabajoFallido;
use App\Support\Fecha;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin TrabajoFallido
 */
final class RecursoTrabajoFallido extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'falloEn' => Fecha::formatear($this->failed_at, 'America/Bogota').', '.Hora::formatear($this->failed_at, 'America/Bogota'),
            'cola' => $this->queue,
            'tipo' => $this->tipo,
            'excepcion' => Str::limit($this->exception, 300),
        ];
    }
}
