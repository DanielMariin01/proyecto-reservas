import axios from 'axios';

export type RangoFinanzas = 'mes' | 'quince_dias' | 'anio';

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export interface IngresoPorMetodo {
    etiqueta: string;
    total: number;
}

export interface PuntoTendencia {
    etiqueta: string;
    ingresos: number;
    gastos: number;
}

export interface GastoFinanzas {
    id: number;
    monto: number;
    descripcion: string;
    fecha: string;
}

export interface Finanzas {
    rango: RangoFinanzas;
    ingresosBrutos: number;
    totalPropinas: number;
    totalGastos: number;
    neto: number;
    ingresosPorMetodo: Record<string, IngresoPorMetodo>;
    tendencia: PuntoTendencia[];
    gastos: GastoFinanzas[];
}

export async function obtenerFinanzas(slug: string, rango: RangoFinanzas): Promise<Finanzas> {
    const respuesta = await axios.get<Finanzas>(`/app/${slug}/api/finanzas`, { params: { rango } });

    return respuesta.data;
}

export async function registrarGasto(
    slug: string,
    datos: { monto: number; descripcion: string; fecha: string },
): Promise<GastoFinanzas> {
    try {
        const respuesta = await axios.post<{ data: GastoFinanzas }>(`/app/${slug}/api/finanzas/gastos`, datos);

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function eliminarGasto(slug: string, gastoId: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/finanzas/gastos/${gastoId}`);
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
