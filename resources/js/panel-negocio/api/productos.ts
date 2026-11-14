import axios from 'axios';

export interface Producto {
    id: number;
    nombre: string;
    descripcion: string | null;
    foto: string | null;
    precio: number;
    visible: boolean;
    orden: number;
}

export interface DatosProducto {
    nombre: string;
    descripcion: string;
    foto: string | null;
    precio: number;
    visible: boolean;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export async function obtenerProductos(slug: string): Promise<Producto[]> {
    const respuesta = await axios.get<{ data: Producto[] }>(`/app/${slug}/api/productos`);

    return respuesta.data.data;
}

export async function crearProducto(slug: string, datos: DatosProducto): Promise<Producto> {
    try {
        const respuesta = await axios.post<{ data: Producto }>(`/app/${slug}/api/productos`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarProducto(slug: string, id: number, datos: DatosProducto): Promise<Producto> {
    try {
        const respuesta = await axios.put<{ data: Producto }>(`/app/${slug}/api/productos/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarProducto(slug: string, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/productos/${id}`);
}

export async function reordenarProductos(slug: string, ids: number[]): Promise<void> {
    await axios.post(`/app/${slug}/api/productos/reordenar`, { ids });
}

export async function subirImagenProducto(slug: string, archivo: File): Promise<{ ruta: string; url: string }> {
    const datos = new FormData();
    datos.append('imagen', archivo);

    const respuesta = await axios.post<{ ruta: string; url: string }>(`/app/${slug}/api/productos/imagenes`, datos, {
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
