import axios from 'axios';

export interface Bloqueo {
    id: number;
    tipo: 'bloqueo' | 'apertura';
    tipoEtiqueta: string;
    fechaInicio: string;
    horaInicio: string;
    fechaFin: string;
    horaFin: string;
    motivo: string | null;
}

export interface DatosBloqueo {
    tipo: string;
    fecha_inicio: string;
    hora_inicio: string;
    fecha_fin: string;
    hora_fin: string;
    motivo: string;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export async function obtenerBloqueos(slug: string, profesionalId: number): Promise<Bloqueo[]> {
    const respuesta = await axios.get<{ data: Bloqueo[] }>(`/app/${slug}/api/profesionales/${profesionalId}/bloqueos`);

    return respuesta.data.data;
}

export async function crearBloqueo(slug: string, profesionalId: number, datos: DatosBloqueo): Promise<Bloqueo> {
    try {
        const respuesta = await axios.post<{ data: Bloqueo }>(`/app/${slug}/api/profesionales/${profesionalId}/bloqueos`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function actualizarBloqueo(slug: string, profesionalId: number, id: number, datos: DatosBloqueo): Promise<Bloqueo> {
    try {
        const respuesta = await axios.put<{ data: Bloqueo }>(`/app/${slug}/api/profesionales/${profesionalId}/bloqueos/${id}`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarBloqueo(slug: string, profesionalId: number, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/profesionales/${profesionalId}/bloqueos/${id}`);
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
