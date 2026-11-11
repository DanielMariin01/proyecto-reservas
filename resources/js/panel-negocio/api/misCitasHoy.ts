import axios, { AxiosError } from 'axios';

export interface AccionesCitaMisCitasHoy {
    puedeMarcarEnAtencion: boolean;
    puedeCompletar: boolean;
    puedeMarcarNoAsistio: boolean;
}

export interface CitaMisCitasHoy {
    id: number;
    hora: string;
    clienteNombre: string | null;
    servicioNombre: string | null;
    notas: string | null;
    estado: string;
    estadoEtiqueta: string;
    precio: number;
    acciones: AccionesCitaMisCitasHoy;
}

export interface MetodoPagoOpcion {
    value: string;
    label: string;
}

export class ErrorDeAccionCita extends Error {}

export function esErrorDeAccionCita(error: unknown): error is ErrorDeAccionCita {
    return error instanceof ErrorDeAccionCita;
}

export async function obtenerMisCitasHoy(slug: string): Promise<{ citas: CitaMisCitasHoy[]; metodosPago: MetodoPagoOpcion[] }> {
    const respuesta = await axios.get<{ citas: CitaMisCitasHoy[]; metodosPago: MetodoPagoOpcion[] }>(`/app/${slug}/api/mis-citas-hoy`);

    return respuesta.data;
}

export async function marcarEnAtencion(slug: string, citaId: number): Promise<CitaMisCitasHoy> {
    return ejecutarAccion(() => axios.post<{ data: CitaMisCitasHoy }>(`/app/${slug}/api/mis-citas-hoy/citas/${citaId}/marcar-en-atencion`));
}

export async function marcarNoAsistio(slug: string, citaId: number): Promise<CitaMisCitasHoy> {
    return ejecutarAccion(() => axios.post<{ data: CitaMisCitasHoy }>(`/app/${slug}/api/mis-citas-hoy/citas/${citaId}/marcar-no-asistio`));
}

export async function completarConPagoPropio(
    slug: string,
    citaId: number,
    datos: { metodo: string; valor: number; propina: number },
): Promise<CitaMisCitasHoy> {
    return ejecutarAccion(() =>
        axios.post<{ data: CitaMisCitasHoy }>(`/app/${slug}/api/mis-citas-hoy/citas/${citaId}/completar-con-pago`, datos),
    );
}

async function ejecutarAccion(peticion: () => Promise<{ data: { data: CitaMisCitasHoy } }>): Promise<CitaMisCitasHoy> {
    try {
        const respuesta = await peticion();

        return respuesta.data.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response?.status === 422) {
            const datos = error.response.data as { mensaje: string };

            throw new ErrorDeAccionCita(datos.mensaje);
        }

        throw error;
    }
}
