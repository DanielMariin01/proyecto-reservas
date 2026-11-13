import axios from 'axios';
import type { CitaConAcciones } from './citaAcciones';

export interface ColumnaAgenda {
    profesionalId: number;
    profesionalNombre: string;
    profesionalColor: string | null;
    profesionalFoto: string | null;
    horaInicio: string | null;
    horaFin: string | null;
    citas: CitaConAcciones[];
}

export interface Agenda {
    fecha: string;
    fechaEtiqueta: string;
    columnas: ColumnaAgenda[];
}

export async function obtenerAgenda(slug: string, fecha: string): Promise<Agenda> {
    const respuesta = await axios.get<Agenda>(`/app/${slug}/api/agenda`, { params: { fecha } });

    return respuesta.data;
}
