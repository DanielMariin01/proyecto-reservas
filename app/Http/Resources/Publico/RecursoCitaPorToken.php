<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\ServicioEnlaceCita;
use App\Support\Fecha;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cita
 */
final class RecursoCitaPorToken extends JsonResource
{
    public function __construct(
        Cita $cita,
        private readonly ServicioEnlaceCita $servicioEnlaceCita,
    ) {
        parent::__construct($cita);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Negocio $negocio */
        $negocio = $request->route('negocio');

        return [
            'servicioId' => $this->servicio_id,
            'profesionalId' => $this->profesional_id,
            'servicio' => Servicio::findOrFail($this->servicio_id)->nombre,
            'profesional' => Profesional::findOrFail($this->profesional_id)->nombre,
            'inicio' => $this->inicio->toIso8601String(),
            'fin' => $this->fin->toIso8601String(),
            'fechaVisible' => Fecha::formatear($this->inicio, $negocio->zona_horaria),
            'horaVisible' => Hora::formatear($this->inicio, $negocio->zona_horaria),
            'precio' => $this->precio,
            'estado' => $this->estado->value,
            'direccion' => $negocio->direccion,
            'whatsapp' => $negocio->whatsapp,
            'nombreNegocio' => $negocio->nombre,
            'enlaceActivo' => $this->servicioEnlaceCita->enlaceActivo($this->resource),
            'puedeCancelar' => $this->servicioEnlaceCita->puedeCancelar($this->resource),
            'puedeReprogramar' => $this->servicioEnlaceCita->puedeReprogramar($this->resource),
        ];
    }
}
