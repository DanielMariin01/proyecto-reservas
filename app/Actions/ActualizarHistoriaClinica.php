<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contratos\ServicioFirmaDigital;
use App\DatosTransferencia\DatosHistoriaClinica;
use App\Events\HistoriaClinicaActualizada;
use App\Models\HistoriaClinica;
use App\Models\Usuario;

final class ActualizarHistoriaClinica
{
    public function __construct(
        private readonly ServicioFirmaDigital $servicioFirmaDigital,
    ) {}

    public function ejecutar(HistoriaClinica $historiaClinica, DatosHistoriaClinica $datos, ?Usuario $actualizadaPor = null): HistoriaClinica
    {
        $carpeta = "historias-clinicas/{$historiaClinica->negocio_id}";

        $historiaClinica->update([
            'cliente_id' => $datos->cliente->id,
            'profesional_id' => $datos->profesional->id,
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
            'consentimiento_en' => $datos->consentimientoAceptado ? ($historiaClinica->consentimiento_en ?? now()) : null,
            'autorizacion_fotos' => $datos->autorizacionFotos,
            'firma_clienta_path' => $this->rutaFirmaActualizada($datos->firmaClientaBase64, $historiaClinica->firma_clienta_path, $carpeta),
            'firma_profesional_path' => $this->rutaFirmaActualizada($datos->firmaProfesionalBase64, $historiaClinica->firma_profesional_path, $carpeta),
        ]);

        HistoriaClinicaActualizada::dispatch($historiaClinica, $actualizadaPor);

        return $historiaClinica;
    }

    /**
     * Si no llega una firma nueva (el pad se deja vacío al editar porque la
     * firma anterior sigue siendo válida), se conserva la que ya había.
     */
    private function rutaFirmaActualizada(?string $imagenBase64, ?string $rutaAnterior, string $carpeta): ?string
    {
        if ($imagenBase64 === null) {
            return $rutaAnterior;
        }

        $this->servicioFirmaDigital->eliminar($rutaAnterior);

        return $this->servicioFirmaDigital->guardar($imagenBase64, $carpeta);
    }
}
