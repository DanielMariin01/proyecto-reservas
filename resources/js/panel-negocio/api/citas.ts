import axios, { AxiosError } from 'axios';
import type { CitaConAcciones } from './citaAcciones';

export interface Cita extends CitaConAcciones {
    inicioVisible: string;
    profesionalNombre: string | null;
    origen: string;
    origenEtiqueta: string;
}

export interface RespuestaPaginada<T> {
    datos: T[];
    paginaActual: number;
    totalPaginas: number;
}

interface RespuestaApi<T> {
    data: T[];
    meta: { current_page: number; last_page: number };
}

interface FiltrosCitas {
    pagina: number;
    profesionalId: string;
    estado: string;
}

export interface DatosCrearCita {
    profesional_id: number | null;
    servicio_id: number;
    fecha: string;
    hora: string;
    nombre_cliente: string;
    celular_cliente: string;
    correo_cliente: string;
    notas: string;
    forzar: boolean;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export class ErrorDeCrearCita extends Error {}

export function esErrorDeCrearCita(error: unknown): error is ErrorDeCrearCita {
    return error instanceof ErrorDeCrearCita;
}

export async function obtenerCitas(slug: string, filtros: FiltrosCitas): Promise<RespuestaPaginada<Cita>> {
    const respuesta = await axios.get<RespuestaApi<Cita>>(`/app/${slug}/api/citas`, {
        params: {
            page: filtros.pagina,
            profesional_id: filtros.profesionalId || undefined,
            estado: filtros.estado || undefined,
        },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function crearCita(slug: string, datos: DatosCrearCita): Promise<Cita> {
    try {
        const respuesta = await axios.post<{ data: Cita }>(`/app/${slug}/api/citas`, datos);

        return respuesta.data.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response?.status === 422) {
            const cuerpo = error.response.data as { errors?: ErroresValidacion; mensaje?: string };

            if (cuerpo.errors) {
                throw new ErrorDeValidacion(cuerpo.errors);
            }

            throw new ErrorDeCrearCita(cuerpo.mensaje ?? 'No se pudo crear la cita.');
        }

        throw error;
    }
}
