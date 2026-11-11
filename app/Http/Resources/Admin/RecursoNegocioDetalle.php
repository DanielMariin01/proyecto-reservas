<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Negocio
 */
final class RecursoNegocioDetalle extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'tipo' => $this->tipo->value,
            'tipoEtiqueta' => $this->tipo->etiqueta(),
            'estadoSuscripcion' => $this->estado_suscripcion->value,
            'estadoSuscripcionEtiqueta' => $this->estado_suscripcion->etiqueta(),
            'proximaFechaPago' => $this->proxima_fecha_pago?->format('d/m/Y'),
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'telefono' => $this->telefono,
            'whatsapp' => $this->whatsapp,
            'zonaHoraria' => $this->zona_horaria,
            'paletaMarcaId' => $this->paleta_marca_id,
            'permiteEditarPagina' => $this->permite_editar_pagina,
            'fechaLimiteRetencionDatos' => $this->fechaLimiteRetencionDeDatos()?->format('d/m/Y'),
            'usuarios' => $this->usuariosConRol()
                ->map(fn (array $fila): array => [
                    'nombre' => $fila['usuario']->nombre,
                    'email' => $fila['usuario']->email,
                    'rol' => $fila['rol'],
                ])
                ->values()
                ->all(),
        ];
    }
}
