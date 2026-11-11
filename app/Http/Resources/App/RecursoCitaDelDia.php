<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Cita;
use App\Models\Negocio;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Fila de la lista "Citas de hoy" del panel del día (RF-REP-01). No es el
 * recurso completo de una cita (eso vive aparte, en la migración de
 * CitaResource): aquí solo van los campos que pinta ese resumen.
 *
 * @mixin Cita
 */
final class RecursoCitaDelDia extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $zonaHoraria = Negocio::actual()->zona_horaria;

        return [
            'id' => $this->id,
            'hora' => Hora::formatear($this->inicio, $zonaHoraria),
            'clienteNombre' => $this->cliente?->nombre,
            'servicioNombre' => $this->servicio?->nombre,
            'profesionalNombre' => $this->profesional?->nombre,
            'estado' => $this->estado->value,
            'estadoEtiqueta' => $this->estado->etiqueta(),
        ];
    }
}
