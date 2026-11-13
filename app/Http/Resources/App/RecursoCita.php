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
 * Una cita, con las acciones disponibles para el usuario actual
 * (ServicioAccionesCita). Compartido entre la lista general de citas
 * (CitaController, RF-RES-06) y la agenda del día agrupada por
 * profesional (AgendaDelDiaController, RF-RES-07) — misma forma, para
 * poder reutilizar los mismos modales de acciones en React.
 *
 * @mixin Cita
 */
final class RecursoCita extends JsonResource
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
        $inicioLocal = $cita->inicio->setTimezone($zonaHoraria);

        return [
            'id' => $cita->id,
            'inicioVisible' => $inicioLocal->format('d/m/Y H:i'),
            'hora' => Hora::formatear($cita->inicio, $zonaHoraria),
            'clienteNombre' => $cita->cliente?->nombre,
            'servicioNombre' => $cita->servicio?->nombre,
            'profesionalNombre' => $cita->profesional?->nombre,
            'notas' => $cita->notas,
            'estado' => $cita->estado->value,
            'estadoEtiqueta' => $cita->estado->etiqueta(),
            'origen' => $cita->origen->value,
            'origenEtiqueta' => $cita->origen->etiqueta(),
            'precio' => $cita->precio,
            'fecha' => $inicioLocal->toDateString(),
            'horaHM' => $inicioLocal->format('H:i'),
            'finHM' => $cita->fin->setTimezone($zonaHoraria)->format('H:i'),
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
