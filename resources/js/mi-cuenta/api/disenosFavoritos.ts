import axios from 'axios';

export interface DisenoFavorito {
    negocioSlug: string;
    negocioNombre: string;
    disenoId: string;
    imagen: string | null;
    titulo: string | null;
    descripcion: string | null;
}

export async function obtenerDisenosFavoritos(): Promise<DisenoFavorito[]> {
    const respuesta = await axios.get<{ data: DisenoFavorito[] }>('/mi-cuenta/api/disenos-favoritos');

    return respuesta.data.data;
}

export async function obtenerIdsFavoritosDelNegocio(slug: string): Promise<string[]> {
    const respuesta = await axios.get<{ data: string[] }>(`/mi-cuenta/api/disenos-favoritos/negocio/${slug}`);

    return respuesta.data.data;
}

export async function alternarDisenoFavorito(negocioSlug: string, disenoId: string): Promise<boolean> {
    const respuesta = await axios.post<{ favorito: boolean }>('/mi-cuenta/api/disenos-favoritos/alternar', {
        negocio_slug: negocioSlug,
        diseno_id: disenoId,
    });

    return respuesta.data.favorito;
}
