import axios, { AxiosError } from 'axios';
import type { CitaPorToken } from '@/tipos';

export class ErrorDeGestionCita extends Error {
    mostrarWhatsapp: boolean;

    constructor(mensaje: string, mostrarWhatsapp: boolean) {
        super(mensaje);
        this.mostrarWhatsapp = mostrarWhatsapp;
    }
}

export function esErrorDeGestionCita(error: unknown): error is ErrorDeGestionCita {
    return error instanceof ErrorDeGestionCita;
}

export async function obtenerCitaPorToken(slug: string, token: string): Promise<CitaPorToken> {
    const respuesta = await axios.get<{ data: CitaPorToken }>(`/api/publico/${slug}/reservas/${token}`);

    return respuesta.data.data;
}

async function gestionar(slug: string, token: string, cuerpo: Record<string, unknown>): Promise<CitaPorToken> {
    try {
        const respuesta = await axios.post<{ data: CitaPorToken }>(`/api/publico/${slug}/reservas/${token}`, cuerpo);

        return respuesta.data.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response?.status === 422) {
            const datos = error.response.data as { mensaje: string; mostrarWhatsapp?: boolean };

            throw new ErrorDeGestionCita(datos.mensaje, datos.mostrarWhatsapp ?? false);
        }

        throw error;
    }
}

export function cancelarCitaPorToken(slug: string, token: string, motivo: string): Promise<CitaPorToken> {
    return gestionar(slug, token, { accion: 'cancelar', motivo: motivo || undefined });
}

export function reprogramarCitaPorToken(slug: string, token: string, inicio: string): Promise<CitaPorToken> {
    return gestionar(slug, token, { accion: 'reprogramar', inicio });
}
