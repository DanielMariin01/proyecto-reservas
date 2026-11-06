import axios from 'axios';
import { ErrorDeGestionCita, esErrorDeGestionCita } from '@/gestionar-cita/api/clienteGestionCita';
import type { CitaDeCuenta, CuentaCliente } from '@/tipos';

export { ErrorDeGestionCita, esErrorDeGestionCita };

export async function obtenerSesion(): Promise<CuentaCliente | null> {
    const respuesta = await axios.get<{ data: CuentaCliente | null }>('/mi-cuenta/api/yo');

    return respuesta.data.data;
}

export async function cerrarSesion(): Promise<void> {
    await axios.post('/mi-cuenta/api/salir');
}

export async function obtenerMisCitas(): Promise<CitaDeCuenta[]> {
    const respuesta = await axios.get<{ data: CitaDeCuenta[] }>('/mi-cuenta/api/citas');

    return respuesta.data.data;
}

async function gestionar(citaId: number, accion: string, cuerpo: Record<string, unknown>): Promise<CitaDeCuenta> {
    try {
        const respuesta = await axios.post<{ data: CitaDeCuenta }>(`/mi-cuenta/api/citas/${citaId}/${accion}`, cuerpo);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { mensaje: string; mostrarWhatsapp?: boolean };

        return new ErrorDeGestionCita(datos.mensaje, datos.mostrarWhatsapp ?? false);
    }

    return error;
}

export function cancelarCitaDeCuenta(citaId: number, motivo: string): Promise<CitaDeCuenta> {
    return gestionar(citaId, 'cancelar', { motivo: motivo || undefined });
}

export function reprogramarCitaDeCuenta(citaId: number, inicio: string): Promise<CitaDeCuenta> {
    return gestionar(citaId, 'reprogramar', { inicio });
}

export function dejarResenaDeCuenta(citaId: number, calificacion: number, comentario: string): Promise<CitaDeCuenta> {
    return gestionar(citaId, 'resena', { calificacion, comentario });
}
