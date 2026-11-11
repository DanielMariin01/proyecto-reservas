import axios from 'axios';

export interface Servicio {
    id: number;
    nombre: string;
    categoriaServicioId: number | null;
    categoriaServicioNombre: string | null;
    descripcion: string | null;
    foto: string | null;
    duracionMinutos: number;
    precio: number;
    margenAntesMinutos: number | null;
    margenDespuesMinutos: number | null;
    intervaloFranjasMinutos: number | null;
    visible: boolean;
    orden: number;
}

export interface DatosServicio {
    nombre: string;
    categoria_servicio_id: number | null;
    descripcion: string;
    foto: string | null;
    duracion_minutos: number;
    precio: number;
    margen_antes_minutos: number | null;
    margen_despues_minutos: number | null;
    intervalo_franjas_minutos: number | null;
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

export async function obtenerServicios(slug: string): Promise<Servicio[]> {
    const respuesta = await axios.get<{ data: Servicio[] }>(`/app/${slug}/api/servicios`);

    return respuesta.data.data;
}

export async function crearServicio(slug: string, datos: DatosServicio): Promise<Servicio> {
    try {
        const respuesta = await axios.post<{ data: Servicio }>(`/app/${slug}/api/servicios`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarServicio(slug: string, id: number, datos: DatosServicio): Promise<Servicio> {
    try {
        const respuesta = await axios.put<{ data: Servicio }>(`/app/${slug}/api/servicios/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarServicio(slug: string, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/servicios/${id}`);
}

export async function reordenarServicios(slug: string, ids: number[]): Promise<void> {
    await axios.post(`/app/${slug}/api/servicios/reordenar`, { ids });
}

export async function subirImagenServicio(slug: string, archivo: File): Promise<{ ruta: string; url: string }> {
    const datos = new FormData();
    datos.append('imagen', archivo);

    const respuesta = await axios.post<{ ruta: string; url: string }>(`/app/${slug}/api/servicios/imagenes`, datos, {
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
