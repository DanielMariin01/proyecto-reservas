import axios from 'axios';

export type RangoFinanzas = 'mes' | 'quince_dias' | 'anio';

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
    const respuesta = await axios.post<{ data: GastoFinanzas }>(`/app/${slug}/api/finanzas/gastos`, datos);

    return respuesta.data.data;
}

export async function eliminarGasto(slug: string, gastoId: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/finanzas/gastos/${gastoId}`);
}
