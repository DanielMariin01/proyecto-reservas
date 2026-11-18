<?php

declare(strict_types=1);

namespace App\Http\Resources\Cliente;

use App\Models\HistoriaClinica;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * El historial clínico propio en /mi-cuenta (RF-HCL-08-a): solo lectura,
 * por eso expone menos que RecursoHistoriaClinica (nada de ids internos
 * que no le sirven al cliente) y suma el nombre del negocio, que aquí sí
 * hace falta porque la lista cruza negocios.
 *
 * @mixin HistoriaClinica
 */
final class RecursoHistoriaClinicaDeCuenta extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $negocio = Negocio::findOrFail($this->negocio_id);

        return [
            'id' => $this->id,
            'nombreNegocio' => $negocio->nombre,
            'fecha' => $this->fecha->toDateString(),
            'profesionalNombre' => $this->profesional?->nombre,
            'observacionesValoracion' => $this->observaciones_valoracion,
            'servicioDiseno' => $this->servicio_diseno->etiqueta(),
            'formaUna' => $this->forma_una->etiqueta(),
            'largoUna' => $this->largo_una->etiqueta(),
            'colores' => $this->colores,
            'decoracionReferencia' => $this->decoracion_referencia,
            'novedadesIncidentes' => $this->novedades_incidentes,
            'proximoMantenimientoSugerido' => $this->proximo_mantenimiento_sugerido?->toDateString(),
            'urlFirmaClienta' => $this->firma_clienta_path !== null
                ? route('mi-cuenta.historial-clinico.firma-clienta', ['historiaClinica' => $this->id])
                : null,
            'urlFirmaProfesional' => $this->firma_profesional_path !== null
                ? route('mi-cuenta.historial-clinico.firma-profesional', ['historiaClinica' => $this->id])
                : null,
        ];
    }
}
