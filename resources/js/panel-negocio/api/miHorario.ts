import axios from 'axios';
import type { Bloqueo } from './bloqueos';

export interface HorarioTrabajo {
    id: number;
    diaSemana: number;
    diaSemanaEtiqueta: string;
    horaInicio: string;
    horaFin: string;
}

export interface DatosBloqueoRapido {
    fecha: string;
    hora_inicio: string;
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

export async function obtenerMiHorario(slug: string): Promise<{ horarios: HorarioTrabajo[]; bloqueos: Bloqueo[] }> {
    const respuesta = await axios.get<{ horarios: HorarioTrabajo[]; bloqueos: Bloqueo[] }>(`/app/${slug}/api/mi-horario`);

    return respuesta.data;
}

export async function crearBloqueoRapido(slug: string, datos: DatosBloqueoRapido): Promise<Bloqueo> {
    try {
        const respuesta = await axios.post<{ data: Bloqueo }>(`/app/${slug}/api/mi-horario/bloqueos`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarBloqueoRapido(slug: string, id: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/mi-horario/bloqueos/${id}`);
}

export async function obtenerMiVistaPreviaDisponibilidad(slug: string, servicioId: number, fecha: string): Promise<string[]> {
    const respuesta = await axios.get<{ franjas: string[] }>(`/app/${slug}/api/mi-horario/vista-previa-disponibilidad`, {
        params: { servicio_id: servicioId, fecha },
    });

    return respuesta.data.franjas;
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
