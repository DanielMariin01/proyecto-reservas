import axios, { AxiosError } from 'axios';

export interface DestinoEstado {
    value: string;
    label: string;
}

export interface AccionesCita {
    puedeCompletarConPago: boolean;
    puedeCambiarEstado: boolean;
    destinosCambioEstado: DestinoEstado[];
    puedeReprogramar: boolean;
    puedeForzarReprogramacion: boolean;
    puedeCancelar: boolean;
    puedeAnularPago: boolean;
}

/**
 * Forma compartida entre la agenda del día (agrupada por profesional) y
 * la lista general de citas: ambas reutilizan los mismos modales de
 * acciones (ModalCambiarEstadoCita, ModalCompletarConPago, ModalMotivo,
 * ModalReprogramarCita).
 */
export interface CitaConAcciones {
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
    finHM: string;
    acciones: AccionesCita;
}

export class ErrorDeAccionCita extends Error {}

export function esErrorDeAccionCita(error: unknown): error is ErrorDeAccionCita {
    return error instanceof ErrorDeAccionCita;
}

export async function cambiarEstadoCita(slug: string, citaId: number, estado: string): Promise<CitaConAcciones> {
    return ejecutarAccion(() => axios.post<{ data: CitaConAcciones }>(`/app/${slug}/api/citas/${citaId}/cambiar-estado`, { estado }));
}

export async function completarConPagoCita(
    slug: string,
    citaId: number,
    datos: { metodo: string; valor: number; propina: number },
): Promise<CitaConAcciones> {
    return ejecutarAccion(() => axios.post<{ data: CitaConAcciones }>(`/app/${slug}/api/citas/${citaId}/completar-con-pago`, datos));
}

export async function cancelarCita(slug: string, citaId: number, motivo: string): Promise<CitaConAcciones> {
    return ejecutarAccion(() => axios.post<{ data: CitaConAcciones }>(`/app/${slug}/api/citas/${citaId}/cancelar`, { motivo }));
}

export async function reprogramarCita(
    slug: string,
    citaId: number,
    datos: { fecha: string; hora: string; forzar: boolean },
): Promise<CitaConAcciones> {
    return ejecutarAccion(() => axios.post<{ data: CitaConAcciones }>(`/app/${slug}/api/citas/${citaId}/reprogramar`, datos));
}

export async function anularPagoCita(slug: string, citaId: number, motivo: string): Promise<CitaConAcciones> {
    return ejecutarAccion(() => axios.post<{ data: CitaConAcciones }>(`/app/${slug}/api/citas/${citaId}/anular-pago`, { motivo }));
}

async function ejecutarAccion(peticion: () => Promise<{ data: { data: CitaConAcciones } }>): Promise<CitaConAcciones> {
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
