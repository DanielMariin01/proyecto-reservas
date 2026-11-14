import axios, { AxiosError } from 'axios';

export interface ItemPedido {
    nombreProducto: string;
    precioUnitario: number;
    cantidad: number;
    subtotal: number;
}

export interface DestinoEstadoPedido {
    value: string;
    label: string;
}

export interface Pedido {
    id: number;
    creadoVisible: string | null;
    clienteNombre: string | null;
    clienteCelular: string | null;
    estado: string;
    estadoEtiqueta: string;
    tipoEntrega: string;
    tipoEntregaEtiqueta: string;
    barrio: string | null;
    nota: string | null;
    total: number;
    items: ItemPedido[];
    destinosCambioEstado: DestinoEstadoPedido[];
    puedeMarcarPagado: boolean;
}

export class ErrorDeAccionPedido extends Error {}

export function esErrorDeAccionPedido(error: unknown): error is ErrorDeAccionPedido {
    return error instanceof ErrorDeAccionPedido;
}

export async function obtenerPedidos(slug: string): Promise<Pedido[]> {
    const respuesta = await axios.get<{ data: Pedido[] }>(`/app/${slug}/api/pedidos`);

    return respuesta.data.data;
}

export async function cambiarEstadoPedido(slug: string, pedidoId: number, estado: string): Promise<Pedido> {
    return ejecutarAccion(() => axios.put<{ data: Pedido }>(`/app/${slug}/api/pedidos/${pedidoId}/estado`, { estado }));
}

export async function marcarPagadoPedido(slug: string, pedidoId: number, datos: { metodo: string; valor: number }): Promise<Pedido> {
    return ejecutarAccion(() => axios.post<{ data: Pedido }>(`/app/${slug}/api/pedidos/${pedidoId}/marcar-pagado`, datos));
}

async function ejecutarAccion(peticion: () => Promise<{ data: { data: Pedido } }>): Promise<Pedido> {
    try {
        const respuesta = await peticion();

        return respuesta.data.data;
    } catch (error) {
        if (error instanceof AxiosError && error.response?.status === 422) {
            const datos = error.response.data as { mensaje: string };

            throw new ErrorDeAccionPedido(datos.mensaje);
        }

        throw error;
    }
}
