<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contratos\ServicioFirmaDigital;
use App\DatosTransferencia\DatosHistoriaClinica;
use App\Events\HistoriaClinicaRegistrada;
use App\Models\HistoriaClinica;
use App\Models\Usuario;

final class CrearHistoriaClinica
{
    public function __construct(
        private readonly ServicioFirmaDigital $servicioFirmaDigital,
    ) {}

    public function ejecutar(DatosHistoriaClinica $datos, ?Usuario $registradaPor = null): HistoriaClinica
    {
        $carpeta = "historias-clinicas/{$datos->cliente->negocio_id}";

        $historiaClinica = HistoriaClinica::create([
            'negocio_id' => $datos->cliente->negocio_id,
            'cliente_id' => $datos->cliente->id,
            'profesional_id' => $datos->profesional->id,
            'usuario_id' => $registradaPor?->id,
            'fecha' => $datos->fecha->toDateString(),
            'alergias' => $datos->alergias,
            'reaccion_anterior' => $datos->reaccionAnterior,
            'diabetes' => $datos->diabetes,
            'problemas_circulacion' => $datos->problemasCirculacion,
            'medicamentos' => $datos->medicamentos,
            'heridas_actuales' => $datos->heridasActuales,
            'otra_condicion' => $datos->otraCondicion,
            'detalle_reaccion' => $datos->detalleReaccion,
            'piel' => $datos->piel,
            'una' => $datos->una,
            'hallazgos_visibles' => $datos->hallazgosVisibles,
            'hallazgo_otro_texto' => $datos->hallazgoOtroTexto,
            'observaciones_valoracion' => $datos->observacionesValoracion,
            'decision' => $datos->decision,
            'motivo_decision' => $datos->motivoDecision,
            'servicio_diseno' => $datos->servicioDiseno,
            'servicio_diseno_otro_texto' => $datos->servicioDisenoOtroTexto,
            'forma_una' => $datos->formaUna,
            'largo_una' => $datos->largoUna,
            'colores' => $datos->colores,
            'decoracion_referencia' => $datos->decoracionReferencia,
            'hora_inicio' => $datos->horaInicio,
            'hora_final' => $datos->horaFinal,
            'tecnica_realizada' => $datos->tecnicaRealizada,
            'productos_marcas' => $datos->productosMarcas,
            'lote_vencimiento' => $datos->loteVencimiento,
            'retiro_previo' => $datos->retiroPrevio,
            'reparaciones' => $datos->reparaciones,
            'reparaciones_detalle' => $datos->reparacionesDetalle,
            'cambios_durante_servicio' => $datos->cambiosDuranteServicio,
            'novedades_incidentes' => $datos->novedadesIncidentes,
            'valor' => $datos->valor,
            'abono' => $datos->abono,
            'saldo' => $datos->saldo,
            'revisio_resultado' => $datos->revisioResultado,
            'recibio_cuidados' => $datos->recibioCuidados,
            'fecha_seguimiento' => $datos->fechaSeguimiento?->toDateString(),
            'proximo_mantenimiento_sugerido' => $datos->proximoMantenimientoSugerido?->toDateString(),
            'novedad_pendiente' => $datos->novedadPendiente,
            'consentimiento_en' => $datos->consentimientoAceptado ? now() : null,
            'autorizacion_fotos' => $datos->autorizacionFotos,
            'firma_clienta_path' => $datos->firmaClientaBase64 !== null
                ? $this->servicioFirmaDigital->guardar($datos->firmaClientaBase64, $carpeta)
                : null,
            'firma_profesional_path' => $datos->firmaProfesionalBase64 !== null
                ? $this->servicioFirmaDigital->guardar($datos->firmaProfesionalBase64, $carpeta)
                : null,
        ]);

        HistoriaClinicaRegistrada::dispatch($historiaClinica, $registradaPor);

        return $historiaClinica;
    }
}
