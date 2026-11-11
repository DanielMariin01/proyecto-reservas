import axios from 'axios';
import type { RespuestaPaginada } from './registroAuditorias';

export interface Negocio {
    id: number;
    nombre: string;
    slug: string;
    tipo: string;
    tipoEtiqueta: string;
    estadoSuscripcion: string;
    estadoSuscripcionEtiqueta: string;
    proximaFechaPago: string | null;
}

export interface UsuarioConRol {
    nombre: string;
    email: string;
    rol: string;
}

export interface NegocioDetalle extends Negocio {
    direccion: string | null;
    ciudad: string | null;
    telefono: string | null;
    whatsapp: string | null;
    zonaHoraria: string;
    paletaMarcaId: number | null;
    permiteEditarPagina: boolean;
    fechaLimiteRetencionDatos: string | null;
    usuarios: UsuarioConRol[];
}

export interface DatosNegocio {
    nombre: string;
    tipo: string;
    zona_horaria: string;
    direccion: string;
    ciudad: string;
    telefono: string;
    whatsapp: string;
    paleta_marca_id: number | null;
    permite_editar_pagina: boolean;
}

export interface DatosCrearNegocio extends DatosNegocio {
    slug: string;
    nombre_dueno: string;
    email_dueno: string;
}

export interface DatosRegistrarPago {
    fecha: string;
    valor: number;
    metodo: string;
    periodo_desde: string;
    periodo_hasta: string;
}

export interface DatosCambiarEstado {
    estado_destino: string;
    motivo: string;
}

export interface PagoSuscripcion {
    id: number;
    fecha: string;
    valor: number;
    metodo: string;
    periodoDesde: string;
    periodoHasta: string;
    registradoPor: string | null;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

interface RespuestaApi<T> {
    data: T[];
    meta: { current_page: number; last_page: number };
}

export async function obtenerNegocios(pagina: number): Promise<RespuestaPaginada<Negocio>> {
    const respuesta = await axios.get<RespuestaApi<Negocio>>('/admin/api/negocios', {
        params: { page: pagina },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function obtenerNegocio(slug: string): Promise<NegocioDetalle> {
    const respuesta = await axios.get<{ data: NegocioDetalle }>(`/admin/api/negocios/${slug}`);

    return respuesta.data.data;
}

export async function crearNegocio(datos: DatosCrearNegocio): Promise<NegocioDetalle> {
    try {
        const respuesta = await axios.post<{ data: NegocioDetalle }>('/admin/api/negocios', datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarNegocio(slug: string, datos: DatosNegocio): Promise<NegocioDetalle> {
    try {
        const respuesta = await axios.put<{ data: NegocioDetalle }>(`/admin/api/negocios/${slug}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarNegocio(slug: string): Promise<void> {
    await axios.delete(`/admin/api/negocios/${slug}`);
}

export async function entrarComoNegocio(slug: string): Promise<string> {
    const respuesta = await axios.post<{ url: string }>(`/admin/api/negocios/${slug}/entrar`);

    return respuesta.data.url;
}

export async function registrarPagoSuscripcion(slug: string, datos: DatosRegistrarPago): Promise<void> {
    try {
        await axios.post(`/admin/api/negocios/${slug}/registrar-pago`, datos);
    } catch (error) {
        throw convertirError(error);
    }
}

export async function cambiarEstadoNegocio(slug: string, datos: DatosCambiarEstado): Promise<void> {
    try {
        await axios.post(`/admin/api/negocios/${slug}/cambiar-estado`, datos);
    } catch (error) {
        throw convertirError(error);
    }
}

export async function obtenerPagosSuscripcion(slug: string, pagina: number): Promise<RespuestaPaginada<PagoSuscripcion>> {
    const respuesta = await axios.get<RespuestaApi<PagoSuscripcion>>(`/admin/api/negocios/${slug}/pagos-suscripcion`, {
        params: { page: pagina },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
