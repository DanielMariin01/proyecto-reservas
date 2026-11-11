import axios from 'axios';
import type { RespuestaPaginada } from './registroAuditorias';

export interface Resena {
    id: number;
    fecha: string;
    negocio: string | null;
    cliente: string | null;
    profesional: string | null;
    calificacion: number;
    comentario: string;
    estado: 'publicada' | 'oculta';
    estadoEtiqueta: string;
    estaPublicada: boolean;
}

interface RespuestaApi<T> {
    data: T[];
    meta: { current_page: number; last_page: number };
}

interface FiltrosResenas {
    pagina: number;
    negocioId: number | null;
    estado: string;
}

export async function obtenerResenas(filtros: FiltrosResenas): Promise<RespuestaPaginada<Resena>> {
    const respuesta = await axios.get<RespuestaApi<Resena>>('/admin/api/resenas', {
        params: {
            page: filtros.pagina,
            negocioId: filtros.negocioId ?? undefined,
            estado: filtros.estado || undefined,
        },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function ocultarResena(id: number): Promise<void> {
    await axios.post(`/admin/api/resenas/${id}/ocultar`);
}

export async function mostrarResena(id: number): Promise<void> {
    await axios.post(`/admin/api/resenas/${id}/mostrar`);
}
