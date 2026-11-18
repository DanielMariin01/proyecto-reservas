import axios from 'axios';

export interface HistoriaClinica {
    id: number;
    codigoClienta: string;
    clienteId: number;
    profesionalId: number;
    profesionalNombre: string | null;
    fecha: string;

    alergias: boolean;
    reaccionAnterior: boolean;
    diabetes: boolean;
    problemasCirculacion: boolean;
    medicamentos: boolean;
    heridasActuales: boolean;
    otraCondicion: string | null;
    detalleReaccion: string | null;

    piel: string[];
    una: string[];
    hallazgosVisibles: string[];
    hallazgoOtroTexto: string | null;
    observacionesValoracion: string | null;
    decision: string;
    motivoDecision: string | null;

    servicioDiseno: string;
    servicioDisenoOtroTexto: string | null;
    formaUna: string;
    largoUna: string;
    colores: string | null;
    decoracionReferencia: string | null;

    horaInicio: string;
    horaFinal: string;
    tecnicaRealizada: string | null;
    productosMarcas: string | null;
    loteVencimiento: string | null;
    retiroPrevio: boolean;
    reparaciones: boolean;
    reparacionesDetalle: string | null;
    cambiosDuranteServicio: string | null;
    novedadesIncidentes: string | null;

    valor: number;
    abono: number;
    saldo: number;
    revisioResultado: boolean;
    recibioCuidados: boolean;
    fechaSeguimiento: string | null;
    proximoMantenimientoSugerido: string | null;
    novedadPendiente: boolean;

    consentimientoAceptado: boolean;
    autorizacionFotos: string;
    tieneFirmaClienta: boolean;
    tieneFirmaProfesional: boolean;
    urlFirmaClienta: string | null;
    urlFirmaProfesional: string | null;
}

export interface DatosHistoriaClinica {
    profesional_id: number;
    fecha: string;

    alergias: boolean;
    reaccion_anterior: boolean;
    diabetes: boolean;
    problemas_circulacion: boolean;
    medicamentos: boolean;
    heridas_actuales: boolean;
    otra_condicion: string;
    detalle_reaccion: string;

    piel: string[];
    una: string[];
    hallazgos_visibles: string[];
    hallazgo_otro_texto: string;
    observaciones_valoracion: string;
    decision: string;
    motivo_decision: string;

    servicio_diseno: string;
    servicio_diseno_otro_texto: string;
    forma_una: string;
    largo_una: string;
    colores: string;
    decoracion_referencia: string;

    hora_inicio: string;
    hora_final: string;
    tecnica_realizada: string;
    productos_marcas: string;
    lote_vencimiento: string;
    retiro_previo: boolean;
    reparaciones: boolean;
    reparaciones_detalle: string;
    cambios_durante_servicio: string;
    novedades_incidentes: string;

    valor: number;
    abono: number;
    saldo: number;
    revisio_resultado: boolean;
    recibio_cuidados: boolean;
    fecha_seguimiento: string;
    proximo_mantenimiento_sugerido: string;
    novedad_pendiente: boolean;

    consentimiento_aceptado: boolean;
    autorizacion_fotos: string;
    firma_clienta_base64: string | null;
    firma_profesional_base64: string | null;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export async function obtenerHistoriasClinicas(slug: string, clienteId: number): Promise<HistoriaClinica[]> {
    const respuesta = await axios.get<{ data: HistoriaClinica[] }>(`/app/${slug}/api/clientes/${clienteId}/historias-clinicas`);

    return respuesta.data.data;
}

export async function crearHistoriaClinica(slug: string, clienteId: number, datos: DatosHistoriaClinica): Promise<HistoriaClinica> {
    try {
        const respuesta = await axios.post<{ data: HistoriaClinica }>(`/app/${slug}/api/clientes/${clienteId}/historias-clinicas`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarHistoriaClinica(
    slug: string,
    clienteId: number,
    id: number,
    datos: DatosHistoriaClinica,
): Promise<HistoriaClinica> {
    try {
        const respuesta = await axios.put<{ data: HistoriaClinica }>(
            `/app/${slug}/api/clientes/${clienteId}/historias-clinicas/${id}`,
            datos,
        );

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarHistoriaClinica(slug: string, clienteId: number, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/clientes/${clienteId}/historias-clinicas/${id}`);
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
