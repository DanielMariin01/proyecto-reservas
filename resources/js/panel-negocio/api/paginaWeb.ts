import axios from 'axios';

export interface BloqueEditable {
    indice: number;
    tipo: string;
    tipoEtiqueta: string;
    data: Record<string, unknown>;
}

export interface DatosBloqueEnviado {
    indice: number;
    data: Record<string, unknown>;
}

export async function obtenerBloquesEditables(slug: string): Promise<BloqueEditable[]> {
    const respuesta = await axios.get<{ bloques: BloqueEditable[] }>(`/app/${slug}/api/pagina`);

    return respuesta.data.bloques;
}

export async function guardarYPublicarPagina(slug: string, bloques: DatosBloqueEnviado[]): Promise<BloqueEditable[]> {
    const respuesta = await axios.post<{ bloques: BloqueEditable[] }>(`/app/${slug}/api/pagina/guardar-y-publicar`, { bloques });

    return respuesta.data.bloques;
}

export async function subirImagenPaginaWeb(slug: string, archivo: File): Promise<{ ruta: string; url: string }> {
    const datos = new FormData();
    datos.append('imagen', archivo);

    const respuesta = await axios.post<{ ruta: string; url: string }>(`/app/${slug}/api/pagina/imagenes`, datos, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });

    return respuesta.data;
}
