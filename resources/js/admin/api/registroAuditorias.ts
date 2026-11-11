import axios from 'axios';

export interface RegistroAuditoria {
    id: number;
    fecha: string;
    negocio: string | null;
    usuario: string;
    accion: string;
    hechoPorSoporte: boolean;
    motivo: string | null;
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

export async function obtenerRegistrosAuditoria(
    pagina: number,
    buscar: string,
    negocioId?: number,
): Promise<RespuestaPaginada<RegistroAuditoria>> {
    const respuesta = await axios.get<RespuestaApi<RegistroAuditoria>>('/admin/api/registro-auditorias', {
        params: { page: pagina, buscar: buscar || undefined, negocioId },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}
