<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Negocio;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CU-04: fila de "Mis citas de hoy", el autoservicio del profesional
 * sobre sus propias citas. Las acciones aquí son solo transiciones de
 * estado simples (sin las reglas de permisos de ServicioAccionesCita,
 * que son para cuando gestiona todas las citas el dueño): esta pantalla
 * ya filtra por "mi propio profesional" antes de llegar aquí.
 *
 * @mixin Cita
 */
final class RecursoCitaMisCitasHoy extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Cita $cita */
        $cita = $this->resource;
        $zonaHoraria = Negocio::actual()->zona_horaria;
        $estado = $cita->obtenerEstado();

        return [
            'id' => $cita->id,
            'hora' => Hora::formatear($cita->inicio, $zonaHoraria),
            'clienteNombre' => $cita->cliente?->nombre,
            'servicioNombre' => $cita->servicio?->nombre,
            'notas' => $cita->notas,
            'estado' => $cita->estado->value,
            'estadoEtiqueta' => $cita->estado->etiqueta(),
            'precio' => $cita->precio,
            'acciones' => [
                'puedeMarcarEnAtencion' => $estado->puedeTransicionarA(EstadoCitaValor::EnAtencion),
                'puedeCompletar' => $estado->puedeTransicionarA(EstadoCitaValor::Completada),
                'puedeMarcarNoAsistio' => $estado->puedeTransicionarA(EstadoCitaValor::NoAsistio),
            ],
        ];
    }
}
