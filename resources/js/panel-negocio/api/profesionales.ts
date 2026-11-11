import axios from 'axios';

export interface Profesional {
    id: number;
    nombre: string;
    especialidad: string | null;
    foto: string | null;
    biografia: string | null;
    certificaciones: string | null;
    color: string | null;
    activo: boolean;
    usuarioEmail: string | null;
}

export interface DatosProfesional {
    nombre: string;
    especialidad: string;
    foto: string | null;
    biografia: string;
    certificaciones: string;
    color: string;
    activo: boolean;
}

export interface DatosCrearProfesional extends DatosProfesional {
    nombre_usuario: string;
    email_usuario: string;
}

export interface DatosVincularUsuario {
    nombre: string;
    email: string;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export async function obtenerProfesionales(slug: string): Promise<Profesional[]> {
    const respuesta = await axios.get<{ data: Profesional[] }>(`/app/${slug}/api/profesionales`);

    return respuesta.data.data;
}

export async function crearProfesional(slug: string, datos: DatosCrearProfesional): Promise<Profesional> {
    try {
        const respuesta = await axios.post<{ data: Profesional }>(`/app/${slug}/api/profesionales`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarProfesional(slug: string, id: number, datos: DatosProfesional): Promise<Profesional> {
    try {
        const respuesta = await axios.put<{ data: Profesional }>(`/app/${slug}/api/profesionales/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarProfesional(slug: string, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/profesionales/${id}`);
}

export async function vincularUsuario(slug: string, id: number, datos: DatosVincularUsuario): Promise<Profesional> {
    try {
        const respuesta = await axios.post<{ data: Profesional }>(`/app/${slug}/api/profesionales/${id}/vincular-usuario`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function subirFotoProfesional(slug: string, archivo: File): Promise<{ ruta: string; url: string }> {
    const datos = new FormData();
    datos.append('imagen', archivo);

    const respuesta = await axios.post<{ ruta: string; url: string }>(`/app/${slug}/api/profesionales/imagenes`, datos, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });

    return respuesta.data;
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
