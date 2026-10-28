<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Support\Fecha;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cita
 */
final class RecursoCitaPublica extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Negocio $negocio */
        $negocio = $request->route('negocio');

        return [
            'id' => $this->id,
            'servicio' => Servicio::findOrFail($this->servicio_id)->nombre,
            'profesional' => Profesional::findOrFail($this->profesional_id)->nombre,
            'cliente' => Cliente::findOrFail($this->cliente_id)->nombre,
            'inicio' => $this->inicio->toIso8601String(),
            'fin' => $this->fin->toIso8601String(),
            'fechaVisible' => Fecha::formatear($this->inicio, $negocio->zona_horaria),
            'horaVisible' => Hora::formatear($this->inicio, $negocio->zona_horaria),
            'precio' => $this->precio,
            'estado' => $this->estado->value,
            'direccion' => $negocio->direccion,
            'whatsapp' => $negocio->whatsapp,
            'nombreNegocio' => $negocio->nombre,
        ];
    }
}
