import axios from 'axios';

export interface EstadisticasCliente {
    citasTotales: number;
    citasPendientes: number;
    citasCompletadas: number;
    citasCanceladas: number;
    citasNoAsistio: number;
}

export async function obtenerEstadisticas(): Promise<EstadisticasCliente> {
    const respuesta = await axios.get<EstadisticasCliente>('/mi-cuenta/api/estadisticas');

    return respuesta.data;
}
