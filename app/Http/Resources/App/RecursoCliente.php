<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cliente
 */
final class RecursoCliente extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigoClienta' => $this->codigoClienta(),
            'nombre' => $this->nombre,
            'celular' => $this->celular,
            'documento' => $this->documento,
            'fechaNacimiento' => $this->fecha_nacimiento?->toDateString(),
            'correo' => $this->correo,
            'notas' => $this->notas,
            'vecesNoAsistio' => $this->veces_no_asistio,
            'citasCount' => $this->citas_count ?? null,
        ];
    }
}
