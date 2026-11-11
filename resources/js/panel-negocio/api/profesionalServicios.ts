import axios from 'axios';

export interface ServicioVinculado {
    id: number;
    nombre: string;
    precioPropio: number | null;
    duracionPropiaMinutos: number | null;
}

export interface ServicioDisponible {
    id: number;
    nombre: string;
}

export interface RespuestaServiciosProfesional {
    vinculados: ServicioVinculado[];
    disponibles: ServicioDisponible[];
}

export interface DatosAttachServicio {
    servicio_id: number;
    precio: number | null;
    duracion_minutos: number | null;
}

export interface DatosPivoteServicio {
    precio: number | null;
    duracion_minutos: number | null;
}

export async function obtenerServiciosDelProfesional(slug: string, profesionalId: number): Promise<RespuestaServiciosProfesional> {
    const respuesta = await axios.get<{ data: ServicioVinculado[]; disponibles: ServicioDisponible[] }>(
        `/app/${slug}/api/profesionales/${profesionalId}/servicios`,
    );

    return { vinculados: respuesta.data.data, disponibles: respuesta.data.disponibles };
}

export async function vincularServicio(slug: string, profesionalId: number, datos: DatosAttachServicio): Promise<void> {
    await axios.post(`/app/${slug}/api/profesionales/${profesionalId}/servicios`, datos);
}

export async function actualizarPivoteServicio(slug: string, profesionalId: number, servicioId: number, datos: DatosPivoteServicio): Promise<void> {
    await axios.put(`/app/${slug}/api/profesionales/${profesionalId}/servicios/${servicioId}`, datos);
}

export async function desvincularServicio(slug: string, profesionalId: number, servicioId: number): Promise<void> {
    await axios.delete(`/app/${slug}/api/profesionales/${profesionalId}/servicios/${servicioId}`);
}
