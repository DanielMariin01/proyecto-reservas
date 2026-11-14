import axios from 'axios';
import type { ColumnaAgenda } from './agenda';

export interface MiAgenda {
    fecha: string;
    fechaEtiqueta: string;
    columna: ColumnaAgenda;
}

export async function obtenerMiAgenda(slug: string, fecha: string): Promise<MiAgenda> {
    const respuesta = await axios.get<MiAgenda>(`/app/${slug}/api/mi-horario/agenda`, { params: { fecha } });

    return respuesta.data;
}
