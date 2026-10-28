import axios, { AxiosError } from 'axios';
import type { CitaCreada, DatosDelCliente, ErrorReserva, HoraDisponible, Profesional, Servicio } from '@/tipos';

export class ErrorDeReservaPublica extends Error {
    horasCercanas: string[];

    constructor(mensaje: string, horasCercanas: string[]) {
        super(mensaje);
        this.horasCercanas = horasCercanas;
    }
}

export function esErrorDeReservaPublica(error: unknown): error is ErrorDeReservaPublica {
    return error instanceof ErrorDeReservaPublica;
}

export async function obtenerServicios(slug: string): Promise<Servicio[]> {
    const respuesta = await axios.get<{ data: Servicio[] }>(`/api/publico/${slug}/servicios`);

    return respuesta.data.data;
}

export async function obtenerProfesionales(slug: string, servicioId: number): Promise<Profesional[]> {
    const respuesta = await axios.get<{ data: Profesional[] }>(`/api/publico/${slug}/profesionales`);

    return respuesta.data.data.filter((profesional) => profesional.servicios.includes(servicioId));
}

export async function obtenerDiasConDisponibilidad(
    slug: string,
    servicioId: number,
    profesionalId: number | null,
    mes: string,
): Promise<string[]> {
    const respuesta = await axios.get<{ dias: string[] }>(`/api/publico/${slug}/disponibilidad`, {
        params: { servicio: servicioId, profesional: profesionalId ?? undefined, mes },
    });

    return respuesta.data.dias;
}

export async function obtenerHorasDisponibles(
    slug: string,
    servicioId: number,
    profesionalId: number | null,
    fecha: string,
): Promise<HoraDisponible[]> {
    const respuesta = await axios.get<{ data: HoraDisponible[] }>(`/api/publico/${slug}/disponibilidad`, {
        params: { servicio: servicioId, profesional: profesionalId ?? undefined, fecha },
    });

    return respuesta.data.data;
}

export async function crearReserva(
    slug: string,
    servicioId: number,
    profesionalId: number | null,
    inicio: string,
    datosDelCliente: DatosDelCliente,
): Promise<CitaCreada> {
    try {
        const respuesta = await axios.post<{ data: CitaCreada }>(`/api/publico/${slug}/reservas`, {
            servicio_id: servicioId,
            profesional_id: profesionalId ?? undefined,
            inicio,
            nombre: datosDelCliente.nombre,
            celular: datosDelCliente.celular,
            correo: datosDelCliente.correo || undefined,
            nota: datosDelCliente.nota || undefined,
            autorizacion_datos: datosDelCliente.autorizacionDatos,
        });

        return respuesta.data.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response?.status === 409) {
            const datos = error.response.data as ErrorReserva;

            throw new ErrorDeReservaPublica(datos.mensaje, datos.horasCercanas);
        }

        throw error;
    }
}
