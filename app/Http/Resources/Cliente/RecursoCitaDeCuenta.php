<?php

declare(strict_types=1);

namespace App\Http\Resources\Cliente;

use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Resena;
use App\Models\Servicio;
use App\Services\ServicioEnlaceCita;
use App\Services\ServicioResenas;
use App\Support\Fecha;
use App\Support\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Misma forma que RecursoCitaPorToken (T5/T9), pero para la lista del
 * panel del cliente (T11): no depende de un {negocio:slug} en la ruta
 * (aquí una sola lista cruza negocios), así que resuelve el Negocio desde
 * la propia cita y suma el identificador de la cita y el slug de su
 * negocio para poder actuar sobre cada una desde la lista.
 *
 * @mixin Cita
 */
final class RecursoCitaDeCuenta extends JsonResource
{
    public function __construct(
        Cita $cita,
        private readonly ServicioEnlaceCita $servicioEnlaceCita,
        private readonly ServicioResenas $servicioResenas,
    ) {
        parent::__construct($cita);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $negocio = Negocio::findOrFail($this->negocio_id);
        $resena = Resena::query()->where('cita_id', $this->id)->first();

        return [
            'id' => $this->id,
            'negocioSlug' => $negocio->slug,
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
            'esProxima' => ! $this->obtenerEstado()->esFinal(),
            'enlaceActivo' => $this->servicioEnlaceCita->enlaceActivo($this->resource),
            'puedeCancelar' => $this->servicioEnlaceCita->puedeCancelar($this->resource),
            'puedeReprogramar' => $this->servicioEnlaceCita->puedeReprogramar($this->resource),
            'puedeDejarResena' => $resena === null && $this->servicioResenas->puedeDejarResena($this->resource),
            'resena' => $resena === null ? null : [
                'calificacion' => $resena->calificacion,
                'comentario' => $resena->comentario,
                'respuestaTexto' => $resena->respuesta_texto,
            ],
        ];
    }
}
