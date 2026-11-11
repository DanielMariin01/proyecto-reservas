import axios from 'axios';
import type { RespuestaPaginada } from './registroAuditorias';

export interface Usuario {
    id: number;
    nombre: string;
    email: string;
    telefono: string | null;
    esSuperAdmin: boolean;
}

export interface DatosUsuario {
    nombre: string;
    email: string;
    telefono: string;
    es_super_admin: boolean;
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

export async function obtenerUsuarios(pagina: number): Promise<RespuestaPaginada<Usuario>> {
    const respuesta = await axios.get<RespuestaApi<Usuario>>('/admin/api/usuarios', {
        params: { page: pagina },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function crearUsuario(datos: DatosUsuario): Promise<Usuario> {
    try {
        const respuesta = await axios.post<{ data: Usuario }>('/admin/api/usuarios', datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarUsuario(id: number, datos: DatosUsuario): Promise<Usuario> {
    try {
        const respuesta = await axios.put<{ data: Usuario }>(`/admin/api/usuarios/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarUsuario(id: number): Promise<void> {
    await axios.delete(`/admin/api/usuarios/${id}`);
}

export async function restablecerContrasenaUsuario(id: number): Promise<void> {
    await axios.post(`/admin/api/usuarios/${id}/restablecer-contrasena`);
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
