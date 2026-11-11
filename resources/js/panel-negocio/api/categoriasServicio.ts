import axios from 'axios';

export interface CategoriaServicio {
    id: number;
    nombre: string;
    orden: number;
    serviciosCount: number;
}

export interface DatosCategoriaServicio {
    nombre: string;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export async function obtenerCategoriasServicio(slug: string): Promise<CategoriaServicio[]> {
    const respuesta = await axios.get<{ data: CategoriaServicio[] }>(`/app/${slug}/api/categorias-servicio`);

    return respuesta.data.data;
}

export async function crearCategoriaServicio(slug: string, datos: DatosCategoriaServicio): Promise<CategoriaServicio> {
    try {
        const respuesta = await axios.post<{ data: CategoriaServicio }>(`/app/${slug}/api/categorias-servicio`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarCategoriaServicio(slug: string, id: number, datos: DatosCategoriaServicio): Promise<CategoriaServicio> {
    try {
        const respuesta = await axios.put<{ data: CategoriaServicio }>(`/app/${slug}/api/categorias-servicio/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarCategoriaServicio(slug: string, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/categorias-servicio/${id}`);
}

export async function reordenarCategoriasServicio(slug: string, ids: number[]): Promise<void> {
    await axios.post(`/app/${slug}/api/categorias-servicio/reordenar`, { ids });
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
