import axios from 'axios';

export interface ResenaNegocio {
    id: number;
    fecha: string;
    cliente: string | null;
    profesional: string | null;
    calificacion: number;
    comentario: string;
    estado: 'publicada' | 'oculta';
    estadoEtiqueta: string;
    estaPublicada: boolean;
    respuestaTexto: string | null;
}

export interface RespuestaPaginada<T> {
    datos: T[];
    paginaActual: number;
    totalPaginas: number;
}

interface RespuestaApi<T> {
    data: T[];
    meta: { current_page: number; last_page: number };
}

interface FiltrosResenas {
    pagina: number;
    estado: string;
    calificacion: string;
}

export async function obtenerResenas(slug: string, filtros: FiltrosResenas): Promise<RespuestaPaginada<ResenaNegocio>> {
    const respuesta = await axios.get<RespuestaApi<ResenaNegocio>>(`/app/${slug}/api/resenas`, {
        params: {
            page: filtros.pagina,
            estado: filtros.estado || undefined,
            calificacion: filtros.calificacion || undefined,
        },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function responderResena(slug: string, id: number, respuesta: string): Promise<ResenaNegocio> {
    const resultado = await axios.post<{ data: ResenaNegocio }>(`/app/${slug}/api/resenas/${id}/responder`, { respuesta });

    return resultado.data.data;
}

export async function ocultarResena(slug: string, id: number): Promise<void> {
    await axios.post(`/app/${slug}/api/resenas/${id}/ocultar`);
}

export async function mostrarResena(slug: string, id: number): Promise<void> {
    await axios.post(`/app/${slug}/api/resenas/${id}/mostrar`);
}
