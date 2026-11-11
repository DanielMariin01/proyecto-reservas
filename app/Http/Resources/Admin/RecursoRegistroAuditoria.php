<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\RegistroAuditoria;
use App\Support\Fecha;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RegistroAuditoria
 */
final class RecursoRegistroAuditoria extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fecha = $this->created_at ?? now();

        return [
            'id' => $this->id,
            'fecha' => Fecha::formatear($fecha, 'America/Bogota').', '.Hora::formatear($fecha, 'America/Bogota'),
            'negocio' => $this->negocio !== null ? $this->negocio->nombre : null,
            'usuario' => $this->usuario !== null ? $this->usuario->nombre : 'Sistema',
            'accion' => $this->accion,
            'hechoPorSoporte' => $this->hecho_por_soporte,
            'motivo' => $this->motivo,
        ];
    }
}
