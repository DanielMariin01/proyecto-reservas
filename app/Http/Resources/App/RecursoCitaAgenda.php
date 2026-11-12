<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Enums\EstadoCitaValor;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Usuario;
use App\Services\ServicioAccionesCita;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Fila de la agenda del día (RF-RES-07). Como RecursoCitaDelDia, es un
 * recorte deliberado: el recurso completo de una cita vive en la futura
 * migración de CitaResource.
 *
 * @mixin Cita
 */
final class RecursoCitaAgenda extends JsonResource
{
    public function __construct(
        Cita $cita,
        private readonly Usuario $usuario,
        private readonly ServicioAccionesCita $servicioAccionesCita,
    ) {
        parent::__construct($cita);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Cita $cita */
        $cita = $this->resource;
        $zonaHoraria = Negocio::actual()->zona_horaria;

        return [
            'id' => $cita->id,
            'hora' => Hora::formatear($cita->inicio, $zonaHoraria),
            'clienteNombre' => $cita->cliente?->nombre,
            'servicioNombre' => $cita->servicio?->nombre,
            'notas' => $cita->notas,
            'estado' => $cita->estado->value,
            'estadoEtiqueta' => $cita->estado->etiqueta(),
            'precio' => $cita->precio,
            'fecha' => $cita->inicio->setTimezone($zonaHoraria)->toDateString(),
            'horaHM' => $cita->inicio->setTimezone($zonaHoraria)->format('H:i'),
            'acciones' => [
                'puedeCompletarConPago' => $this->servicioAccionesCita->puedeCompletarConPago($cita, $this->usuario),
                'puedeCambiarEstado' => $this->servicioAccionesCita->puedeCambiarEstado($cita, $this->usuario),
                'destinosCambioEstado' => array_map(
                    fn (EstadoCitaValor $destino): array => ['value' => $destino->value, 'label' => $destino->etiqueta()],
                    $this->servicioAccionesCita->destinosPosiblesDeCambioDeEstado($cita),
                ),
                'puedeReprogramar' => $this->servicioAccionesCita->puedeReprogramar($cita, $this->usuario),
                'puedeForzarReprogramacion' => $this->servicioAccionesCita->puedeForzarReprogramacion($this->usuario),
                'puedeCancelar' => $this->servicioAccionesCita->puedeCancelar($cita, $this->usuario),
                'puedeAnularPago' => $this->servicioAccionesCita->puedeAnularPago($cita, $this->usuario),
            ],
        ];
    }
}
