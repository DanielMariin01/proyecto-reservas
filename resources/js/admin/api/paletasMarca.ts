import axios from 'axios';
import type { RespuestaPaginada } from './registroAuditorias';

export interface PaletaMarca {
    id: number;
    nombre: string;
    colorPrimario: string;
    colorSecundario: string;
    negociosCount: number;
}

export interface DatosPaletaMarca {
    nombre: string;
    color_primario: string;
    color_secundario: string;
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

export async function obtenerPaletasMarca(pagina: number, buscar: string): Promise<RespuestaPaginada<PaletaMarca>> {
    const respuesta = await axios.get<RespuestaApi<PaletaMarca>>('/admin/api/paleta-marcas', {
        params: { page: pagina, buscar: buscar || undefined },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function crearPaletaMarca(datos: DatosPaletaMarca): Promise<PaletaMarca> {
    try {
        const respuesta = await axios.post<{ data: PaletaMarca }>('/admin/api/paleta-marcas', datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarPaletaMarca(id: number, datos: DatosPaletaMarca): Promise<PaletaMarca> {
    try {
        const respuesta = await axios.put<{ data: PaletaMarca }>(`/admin/api/paleta-marcas/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarPaletaMarca(id: number): Promise<void> {
    await axios.delete(`/admin/api/paleta-marcas/${id}`);
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
