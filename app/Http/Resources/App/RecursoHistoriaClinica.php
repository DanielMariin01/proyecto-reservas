<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\HistoriaClinica;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HistoriaClinica
 */
final class RecursoHistoriaClinica extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigoClienta' => $this->codigoClienta(),
            'clienteId' => $this->cliente_id,
            'profesionalId' => $this->profesional_id,
            'profesionalNombre' => $this->profesional?->nombre,
            'fecha' => $this->fecha->toDateString(),

            'alergias' => $this->alergias,
            'reaccionAnterior' => $this->reaccion_anterior,
            'diabetes' => $this->diabetes,
            'problemasCirculacion' => $this->problemas_circulacion,
            'medicamentos' => $this->medicamentos,
            'heridasActuales' => $this->heridas_actuales,
            'otraCondicion' => $this->otra_condicion,
            'detalleReaccion' => $this->detalle_reaccion,

            'piel' => $this->piel,
            'una' => $this->una,
            'hallazgosVisibles' => $this->hallazgos_visibles,
            'hallazgoOtroTexto' => $this->hallazgo_otro_texto,
            'observacionesValoracion' => $this->observaciones_valoracion,
            'decision' => $this->decision->value,
            'motivoDecision' => $this->motivo_decision,

            'servicioDiseno' => $this->servicio_diseno->value,
            'servicioDisenoOtroTexto' => $this->servicio_diseno_otro_texto,
            'formaUna' => $this->forma_una->value,
            'largoUna' => $this->largo_una->value,
            'colores' => $this->colores,
            'decoracionReferencia' => $this->decoracion_referencia,

            'horaInicio' => $this->hora_inicio,
            'horaFinal' => $this->hora_final,
            'tecnicaRealizada' => $this->tecnica_realizada,
            'productosMarcas' => $this->productos_marcas,
            'loteVencimiento' => $this->lote_vencimiento,
            'retiroPrevio' => $this->retiro_previo,
            'reparaciones' => $this->reparaciones,
            'reparacionesDetalle' => $this->reparaciones_detalle,
            'cambiosDuranteServicio' => $this->cambios_durante_servicio,
            'novedadesIncidentes' => $this->novedades_incidentes,

            'valor' => $this->valor,
            'abono' => $this->abono,
            'saldo' => $this->saldo,
            'revisionResultado' => $this->revision_resultado,
            'recibioCuidados' => $this->recibio_cuidados,
            'fechaSeguimiento' => $this->fecha_seguimiento?->toDateString(),
            'proximoMantenimientoSugerido' => $this->proximo_mantenimiento_sugerido?->toDateString(),
            'novedadPendiente' => $this->novedad_pendiente,

            'consentimientoAceptado' => $this->consentimiento_en !== null,
            'autorizacionFotos' => $this->autorizacion_fotos->value,
            'tieneFirmaClienta' => $this->firma_clienta_path !== null,
            'tieneFirmaProfesional' => $this->firma_profesional_path !== null,
            'urlFirmaClienta' => $this->firma_clienta_path !== null
                ? route('app.historias-clinicas.firma-clienta', ['negocio' => Negocio::actual()->slug, 'historiaClinica' => $this->id])
                : null,
            'urlFirmaProfesional' => $this->firma_profesional_path !== null
                ? route('app.historias-clinicas.firma-profesional', ['negocio' => Negocio::actual()->slug, 'historiaClinica' => $this->id])
                : null,
        ];
    }
}
