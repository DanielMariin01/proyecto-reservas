import axios from 'axios';

export interface CategoriaProducto {
    id: number;
    nombre: string;
    orden: number;
    productosCount: number;
}

export interface DatosCategoriaProducto {
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

export async function obtenerCategoriasProducto(slug: string): Promise<CategoriaProducto[]> {
    const respuesta = await axios.get<{ data: CategoriaProducto[] }>(`/app/${slug}/api/categorias-producto`);

    return respuesta.data.data;
}

export async function crearCategoriaProducto(slug: string, datos: DatosCategoriaProducto): Promise<CategoriaProducto> {
    try {
        const respuesta = await axios.post<{ data: CategoriaProducto }>(`/app/${slug}/api/categorias-producto`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarCategoriaProducto(slug: string, id: number, datos: DatosCategoriaProducto): Promise<CategoriaProducto> {
    try {
        const respuesta = await axios.put<{ data: CategoriaProducto }>(`/app/${slug}/api/categorias-producto/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarCategoriaProducto(slug: string, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/categorias-producto/${id}`);
}

export async function reordenarCategoriasProducto(slug: string, ids: number[]): Promise<void> {
    await axios.post(`/app/${slug}/api/categorias-producto/reordenar`, { ids });
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
