import axios, { AxiosError } from 'axios';

export interface DestinoEstado {
    value: string;
    label: string;
}

export interface AccionesCitaAgenda {
    puedeCompletarConPago: boolean;
    puedeCambiarEstado: boolean;
    destinosCambioEstado: DestinoEstado[];
    puedeReprogramar: boolean;
    puedeForzarReprogramacion: boolean;
    puedeCancelar: boolean;
    puedeAnularPago: boolean;
}

export interface CitaAgenda {
    id: number;
    hora: string;
    clienteNombre: string | null;
    servicioNombre: string | null;
    notas: string | null;
    estado: string;
    estadoEtiqueta: string;
    precio: number;
    fecha: string;
    horaHM: string;
    acciones: AccionesCitaAgenda;
}

export interface ColumnaAgenda {
    profesionalId: number;
    profesionalNombre: string;
    profesionalColor: string | null;
    citas: CitaAgenda[];
}

export interface Agenda {
    fecha: string;
    fechaEtiqueta: string;
    columnas: ColumnaAgenda[];
}

export class ErrorDeAccionCita extends Error {}

export function esErrorDeAccionCita(error: unknown): error is ErrorDeAccionCita {
    return error instanceof ErrorDeAccionCita;
}

export async function obtenerAgenda(slug: string, fecha: string): Promise<Agenda> {
    const respuesta = await axios.get<Agenda>(`/app/${slug}/api/agenda`, { params: { fecha } });

    return respuesta.data;
}

export async function cambiarEstadoCitaAgenda(slug: string, citaId: number, estado: string): Promise<CitaAgenda> {
    return ejecutarAccion(() => axios.post<{ data: CitaAgenda }>(`/app/${slug}/api/agenda/citas/${citaId}/cambiar-estado`, { estado }));
}

export async function completarConPagoAgenda(
    slug: string,
    citaId: number,
    datos: { metodo: string; valor: number; propina: number },
): Promise<CitaAgenda> {
    return ejecutarAccion(() => axios.post<{ data: CitaAgenda }>(`/app/${slug}/api/agenda/citas/${citaId}/completar-con-pago`, datos));
}

export async function cancelarCitaAgenda(slug: string, citaId: number, motivo: string): Promise<CitaAgenda> {
    return ejecutarAccion(() => axios.post<{ data: CitaAgenda }>(`/app/${slug}/api/agenda/citas/${citaId}/cancelar`, { motivo }));
}

export async function reprogramarCitaAgenda(
    slug: string,
    citaId: number,
    datos: { fecha: string; hora: string; forzar: boolean },
): Promise<CitaAgenda> {
    return ejecutarAccion(() => axios.post<{ data: CitaAgenda }>(`/app/${slug}/api/agenda/citas/${citaId}/reprogramar`, datos));
}

export async function anularPagoAgenda(slug: string, citaId: number, motivo: string): Promise<CitaAgenda> {
    return ejecutarAccion(() => axios.post<{ data: CitaAgenda }>(`/app/${slug}/api/agenda/citas/${citaId}/anular-pago`, { motivo }));
}

async function ejecutarAccion(peticion: () => Promise<{ data: { data: CitaAgenda } }>): Promise<CitaAgenda> {
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
