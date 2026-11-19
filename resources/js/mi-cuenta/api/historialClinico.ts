import axios from 'axios';

export interface HistoriaClinicaDeCuenta {
    id: number;
    nombreNegocio: string;
    fecha: string;
    profesionalNombre: string | null;
    observacionesValoracion: string | null;
    servicioDiseno: string;
    formaUna: string;
    largoUna: string;
    colores: string | null;
    decoracionReferencia: string | null;
    novedadesIncidentes: string | null;
    proximoMantenimientoSugerido: string | null;
    urlFirmaClienta: string | null;
    urlFirmaProfesional: string | null;
}

export async function obtenerHistorialClinico(): Promise<HistoriaClinicaDeCuenta[]> {
    const respuesta = await axios.get<{ data: HistoriaClinicaDeCuenta[] }>('/mi-cuenta/api/historial-clinico');

    return respuesta.data.data;
}
