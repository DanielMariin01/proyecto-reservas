import axios from 'axios';
import type { RespuestaPaginada } from './registroAuditorias';

export interface RegistroError {
    id: number;
    fecha: string;
    negocio: string | null;
    usuario: string;
    claseExcepcion: string;
    mensaje: string;
    codigoReferencia: string;
    estadoHttp: number;
}

export interface OpcionNegocio {
    id: number;
    nombre: string;
}

interface RespuestaApi<T> {
    data: T[];
    meta: { current_page: number; last_page: number };
}

interface FiltrosRegistroErrores {
    pagina: number;
    buscar: string;
    negocioId: number | null;
    ultimas24h: boolean;
}

export async function obtenerRegistrosError(filtros: FiltrosRegistroErrores): Promise<RespuestaPaginada<RegistroError>> {
    const respuesta = await axios.get<RespuestaApi<RegistroError>>('/admin/api/registro-errores', {
        params: {
            page: filtros.pagina,
            buscar: filtros.buscar || undefined,
            negocioId: filtros.negocioId ?? undefined,
            ultimas24h: filtros.ultimas24h ? 1 : 0,
        },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function obtenerOpcionesNegocio(): Promise<OpcionNegocio[]> {
    const respuesta = await axios.get<{ data: OpcionNegocio[] }>('/admin/api/negocios-opciones');

    return respuesta.data.data;
}
