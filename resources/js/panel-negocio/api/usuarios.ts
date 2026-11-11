import axios from 'axios';

export interface UsuarioNegocio {
    id: number;
    nombre: string;
    email: string;
    telefono: string | null;
    rol: string | null;
}

export interface DatosInvitarUsuario {
    nombre: string;
    email: string;
    rol: string;
}

export interface DatosActualizarUsuario {
    nombre: string;
    telefono: string;
    rol: string;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export async function obtenerUsuariosNegocio(slug: string): Promise<UsuarioNegocio[]> {
    const respuesta = await axios.get<{ data: UsuarioNegocio[] }>(`/app/${slug}/api/usuarios`);

    return respuesta.data.data;
}

export async function invitarUsuario(slug: string, datos: DatosInvitarUsuario): Promise<UsuarioNegocio> {
    try {
        const respuesta = await axios.post<{ data: UsuarioNegocio }>(`/app/${slug}/api/usuarios`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarUsuarioNegocio(slug: string, id: number, datos: DatosActualizarUsuario): Promise<UsuarioNegocio> {
    try {
        const respuesta = await axios.put<{ data: UsuarioNegocio }>(`/app/${slug}/api/usuarios/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarUsuarioNegocio(slug: string, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/usuarios/${id}`);
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
