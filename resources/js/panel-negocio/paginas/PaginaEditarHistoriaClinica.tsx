import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import Alerta from '@/componentes/Alerta';
import { obtenerCliente } from '../api/clientes';
import {
    actualizarHistoriaClinica,
    type DatosHistoriaClinica,
    type HistoriaClinica,
    obtenerHistoriasClinicas,
} from '../api/historiasClinicas';
import { obtenerProfesionales } from '../api/profesionales';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioHistoriaClinica from './FormularioHistoriaClinica';

const ESPERA_ANTES_DE_VOLVER_MS = 1200;

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    clienteId: number;
    historiaClinicaId: number;
}

function haciaDatosEscritura(historia: HistoriaClinica): DatosHistoriaClinica {
    return {
        profesional_id: historia.profesionalId,
        fecha: historia.fecha,
        alergias: historia.alergias,
        reaccion_anterior: historia.reaccionAnterior,
        diabetes: historia.diabetes,
        problemas_circulacion: historia.problemasCirculacion,
        medicamentos: historia.medicamentos,
        heridas_actuales: historia.heridasActuales,
        otra_condicion: historia.otraCondicion ?? '',
        detalle_reaccion: historia.detalleReaccion ?? '',
        piel: historia.piel,
        una: historia.una,
        hallazgos_visibles: historia.hallazgosVisibles,
        hallazgo_otro_texto: historia.hallazgoOtroTexto ?? '',
        observaciones_valoracion: historia.observacionesValoracion ?? '',
        decision: historia.decision,
        motivo_decision: historia.motivoDecision ?? '',
        servicio_diseno: historia.servicioDiseno,
        servicio_diseno_otro_texto: historia.servicioDisenoOtroTexto ?? '',
        forma_una: historia.formaUna,
        largo_una: historia.largoUna,
        colores: historia.colores ?? '',
        decoracion_referencia: historia.decoracionReferencia ?? '',
        hora_inicio: historia.horaInicio,
        hora_final: historia.horaFinal,
        tecnica_realizada: historia.tecnicaRealizada ?? '',
        productos_marcas: historia.productosMarcas ?? '',
        lote_vencimiento: historia.loteVencimiento ?? '',
        retiro_previo: historia.retiroPrevio,
        reparaciones: historia.reparaciones,
        reparaciones_detalle: historia.reparacionesDetalle ?? '',
        cambios_durante_servicio: historia.cambiosDuranteServicio ?? '',
        novedades_incidentes: historia.novedadesIncidentes ?? '',
        valor: historia.valor,
        abono: historia.abono,
        saldo: historia.saldo,
        revision_resultado: historia.revisionResultado,
        recibio_cuidados: historia.recibioCuidados,
        fecha_seguimiento: historia.fechaSeguimiento ?? '',
        proximo_mantenimiento_sugerido: historia.proximoMantenimientoSugerido ?? '',
        novedad_pendiente: historia.novedadPendiente,
        consentimiento_aceptado: historia.consentimientoAceptado,
        autorizacion_fotos: historia.autorizacionFotos,
        firma_clienta_base64: null,
        firma_profesional_base64: null,
    };
}

export default function PaginaEditarHistoriaClinica({
    negocioNombre,
    negocioSlug,
    urlSalir,
    tokenCsrf,
    clienteId,
    historiaClinicaId,
}: Propiedades): ReactElement {
    const consultaCliente = useQuery({
        queryKey: ['panel-cliente', negocioSlug, clienteId],
        queryFn: () => obtenerCliente(negocioSlug, clienteId),
    });

    const consultaProfesionales = useQuery({
        queryKey: ['panel-profesionales', negocioSlug],
        queryFn: () => obtenerProfesionales(negocioSlug),
    });

    const consultaHistorias = useQuery({
        queryKey: ['panel-historias-clinicas', negocioSlug, clienteId],
        queryFn: () => obtenerHistoriasClinicas(negocioSlug, clienteId),
    });

    const historia = consultaHistorias.data?.find((h) => h.id === historiaClinicaId);

    const [guardadoConExito, setGuardadoConExito] = useState(false);

    const mutacion = useMutation({
        mutationFn: (datos: DatosHistoriaClinica) => actualizarHistoriaClinica(negocioSlug, clienteId, historiaClinicaId, datos),
        onSuccess: () => {
            setGuardadoConExito(true);
            setTimeout(() => {
                window.location.href = `/app/${negocioSlug}/clientes/${clienteId}`;
            }, ESPERA_ANTES_DE_VOLVER_MS);
        },
    });

    function volver(): void {
        window.location.href = `/app/${negocioSlug}/clientes/${clienteId}`;
    }

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Editar registro de historia clínica</h2>

                {guardadoConExito && <Alerta tipo="exito">Cambios guardados correctamente.</Alerta>}

                {(consultaCliente.isLoading || consultaProfesionales.isLoading || consultaHistorias.isLoading) && (
                    <div className="h-28 max-w-3xl animate-pulse rounded-tarjeta bg-gris-claro" />
                )}

                {consultaCliente.data && consultaProfesionales.data && historia && (
                    <FormularioHistoriaClinica
                        cliente={consultaCliente.data}
                        profesionales={consultaProfesionales.data}
                        valoresIniciales={haciaDatosEscritura(historia)}
                        firmaClientaExistenteUrl={historia.urlFirmaClienta}
                        firmaProfesionalExistenteUrl={historia.urlFirmaProfesional}
                        enviando={mutacion.isPending || guardadoConExito}
                        textoBoton="Guardar cambios"
                        onGuardar={(datos) => mutacion.mutateAsync(datos)}
                        onCancelar={volver}
                    />
                )}
            </div>
        </LayoutPanelNegocio>
    );
}
