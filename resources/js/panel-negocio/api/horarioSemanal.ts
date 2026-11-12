import axios, { AxiosError } from 'axios';

export interface RangoHorario {
    hora_inicio: string;
    hora_fin: string;
}

export interface DiaHorario {
    disponible: boolean;
    rangos: RangoHorario[];
}

export type DiasHorario = Record<string, DiaHorario>;

export class ErrorDeHorario extends Error {}

export function esErrorDeHorario(error: unknown): error is ErrorDeHorario {
    return error instanceof ErrorDeHorario;
}

export async function obtenerHorarioSemanal(slug: string, profesionalId: number): Promise<DiasHorario> {
    const respuesta = await axios.get<{ dias: DiasHorario }>(`/app/${slug}/api/profesionales/${profesionalId}/horario`);

    return respuesta.data.dias;
}

export async function actualizarHorarioSemanal(slug: string, profesionalId: number, dias: DiasHorario): Promise<DiasHorario> {
    try {
        const respuesta = await axios.put<{ dias: DiasHorario }>(`/app/${slug}/api/profesionales/${profesionalId}/horario`, { dias });

        return respuesta.data.dias;
    } catch (error) {
        if (error instanceof AxiosError && error.response?.status === 422) {
            const datos = error.response.data as { mensaje: string };

            throw new ErrorDeHorario(datos.mensaje);
        }

        throw error;
    }
}

export async function obtenerVistaPreviaDisponibilidad(slug: string, profesionalId: number, servicioId: number, fecha: string): Promise<string[]> {
    const respuesta = await axios.get<{ franjas: string[] }>(`/app/${slug}/api/profesionales/${profesionalId}/vista-previa-disponibilidad`, {
        params: { servicio_id: servicioId, fecha },
    });

    return respuesta.data.franjas;
}
