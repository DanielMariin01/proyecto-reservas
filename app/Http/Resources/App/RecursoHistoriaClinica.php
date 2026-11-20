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

            'horaInicio' => self::sinSegundos($this->hora_inicio),
            'horaFinal' => self::sinSegundos($this->hora_final),
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

    /**
     * `hora_inicio`/`hora_final` son columnas `time` de MariaDB
     * (2026_11_17_090100_crear_tabla_historias_clinicas.php): sin un
     * cast, Eloquent devuelve el valor crudo del driver, que para una
     * columna `time` llega con segundos ("10:00:00"). El formulario de
     * edición reenvía ese mismo valor al guardar, pero
     * SolicitudHistoriaClinica exige `date_format:H:i` (sin segundos),
     * así que un registro nunca se podía volver a guardar sin cambiar
     * manualmente la hora primero. Se normaliza aquí, en el límite de
     * la API, para que el formato de salida siempre coincida con el
     * que la validación de entrada exige.
     */
    private static function sinSegundos(string $hora): string
    {
        return substr($hora, 0, 5);
    }
}
