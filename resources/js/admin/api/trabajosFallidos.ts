import axios from 'axios';
import type { RespuestaPaginada } from './registroAuditorias';

export interface TrabajoFallido {
    id: number;
    falloEn: string;
    cola: string;
    tipo: string;
    excepcion: string;
}

interface RespuestaApi<T> {
    data: T[];
    meta: { current_page: number; last_page: number };
}

export async function obtenerTrabajosFallidos(pagina: number): Promise<RespuestaPaginada<TrabajoFallido>> {
    const respuesta = await axios.get<RespuestaApi<TrabajoFallido>>('/admin/api/trabajos-fallidos', { params: { page: pagina } });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function reintentarTrabajoFallido(id: number): Promise<void> {
    await axios.post(`/admin/api/trabajos-fallidos/${id}/reintentar`);
}
