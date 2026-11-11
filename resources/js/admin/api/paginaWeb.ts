import axios from 'axios';

export interface Bloque {
    type: string;
    data: Record<string, unknown>;
}

export interface PaginaWeb {
    bloques: Bloque[];
    estaPublicada: boolean;
    publicadaEn: string | null;
    hayCambiosSinPublicar: boolean;
}

export interface ErroresValidacion {
    [campo: string]: string[];
}

export class ErrorDeValidacion extends Error {
    constructor(public errores: ErroresValidacion) {
        super('Error de validación');
    }
}

export async function obtenerPaginaWeb(slug: string): Promise<PaginaWeb> {
    const respuesta = await axios.get<{ data: PaginaWeb }>(`/admin/api/negocios/${slug}/pagina`);

    return respuesta.data.data;
}

export async function actualizarBloquesPagina(slug: string, bloques: Bloque[]): Promise<PaginaWeb> {
    try {
        const respuesta = await axios.put<{ data: PaginaWeb }>(`/admin/api/negocios/${slug}/pagina`, { bloques });

        return respuesta.data.data;
    } catch (error) {
        throw convertirError(error);
    }
}

export async function publicarPagina(slug: string): Promise<PaginaWeb> {
    const respuesta = await axios.post<{ data: PaginaWeb }>(`/admin/api/negocios/${slug}/pagina/publicar`);

    return respuesta.data.data;
}

export async function subirImagenPagina(slug: string, archivo: File): Promise<{ ruta: string; url: string }> {
    const datos = new FormData();
    datos.append('imagen', archivo);

    const respuesta = await axios.post<{ ruta: string; url: string }>(`/admin/api/negocios/${slug}/pagina/imagenes`, datos, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });

    return respuesta.data;
}

function convertirError(error: unknown): unknown {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
        const datos = error.response.data as { errors: ErroresValidacion };

        return new ErrorDeValidacion(datos.errors);
    }

    return error;
}
