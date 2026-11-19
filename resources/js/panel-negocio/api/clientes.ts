import axios from 'axios';

export interface Cliente {
    id: number;
    codigoClienta: string;
    nombre: string;
    celular: string;
    documento: string | null;
    fechaNacimiento: string | null;
    correo: string | null;
    notas: string | null;
    vecesNoAsistio: number;
    citasCount: number | null;
}

export interface RespuestaPaginada<T> {
    datos: T[];
    paginaActual: number;
    totalPaginas: number;
}

interface RespuestaApi<T> {
    data: T[];
    meta: { current_page: number; last_page: number };
}

export async function obtenerClientes(slug: string, buscar: string, pagina: number): Promise<RespuestaPaginada<Cliente>> {
    const respuesta = await axios.get<RespuestaApi<Cliente>>(`/app/${slug}/api/clientes`, {
        params: { buscar: buscar || undefined, page: pagina },
    });

    return {
        datos: respuesta.data.data,
        paginaActual: respuesta.data.meta.current_page,
        totalPaginas: respuesta.data.meta.last_page,
    };
}

export async function obtenerCliente(slug: string, id: number): Promise<Cliente> {
    const respuesta = await axios.get<{ data: Cliente }>(`/app/${slug}/api/clientes/${id}`);

    return respuesta.data.data;
}
