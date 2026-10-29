import axios from 'axios';
import type { PaginaPublica } from '@/tipos';

export async function obtenerPagina(slug: string, vistaPrevia: boolean): Promise<PaginaPublica> {
    const respuesta = await axios.get<{ data: PaginaPublica }>(`/api/publico/${slug}/pagina`, {
        params: vistaPrevia ? { vista_previa: 1 } : undefined,
    });

    return respuesta.data.data;
}
