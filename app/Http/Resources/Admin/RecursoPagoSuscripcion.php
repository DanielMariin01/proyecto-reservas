<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\PagoSuscripcion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PagoSuscripcion
 */
final class RecursoPagoSuscripcion extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha->format('d/m/Y'),
            'valor' => $this->valor,
            'metodo' => $this->metodo,
            'periodoDesde' => $this->periodo_desde->format('d/m/Y'),
            'periodoHasta' => $this->periodo_hasta->format('d/m/Y'),
            'registradoPor' => $this->registradoPor?->nombre,
        ];
    }
}
