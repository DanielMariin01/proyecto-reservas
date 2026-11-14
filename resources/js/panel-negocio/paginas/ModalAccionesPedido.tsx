import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import Modal from '@/componentes/Modal';
import { cambiarEstadoPedido, esErrorDeAccionPedido, marcarPagadoPedido, type Pedido } from '../api/pedidos';

interface Propiedades {
    slug: string;
    pedido: Pedido;
    onExito: () => void;
    onCerrar: () => void;
}

const METODOS_PAGO = [
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'nequi', label: 'Nequi' },
    { value: 'daviplata', label: 'Daviplata' },
    { value: 'transferencia', label: 'Transferencia' },
    { value: 'tarjeta', label: 'Tarjeta (datáfono)' },
];

export default function ModalAccionesPedido({ slug, pedido, onExito, onCerrar }: Propiedades): ReactElement {
    const [metodo, setMetodo] = useState('efectivo');
    const [valor, setValor] = useState(String(pedido.total));
    const [error, setError] = useState<string | null>(null);

    function manejarError(error: unknown): void {
        setError(esErrorDeAccionPedido(error) ? error.message : 'Algo falló. Código REF-PED0.');
    }

    const mutacionCambiarEstado = useMutation({
        mutationFn: (estado: string) => cambiarEstadoPedido(slug, pedido.id, estado),
        onSuccess: onExito,
        onError: manejarError,
    });

    const mutacionMarcarPagado = useMutation({
        mutationFn: () => marcarPagadoPedido(slug, pedido.id, { metodo, valor: Number(valor) }),
        onSuccess: onExito,
        onError: manejarError,
    });

    return (
        <Modal titulo="Acciones del pedido" onCerrar={onCerrar}>
            <div className="space-y-4">
                <div>
                    <p className="text-sm font-semibold text-tinta">
                        {pedido.clienteNombre ?? '—'} · {pedido.creadoVisible}
                    </p>
                    <ul className="mt-1 text-sm text-texto-suave">
                        {pedido.items.map((item, indice) => (
                            <li key={indice}>
                                {item.cantidad} × {item.nombreProducto} — ${item.subtotal.toLocaleString('es-CO')}
                            </li>
                        ))}
                    </ul>
                    <p className="mt-1 text-sm font-semibold text-tinta">Total: ${pedido.total.toLocaleString('es-CO')}</p>
                    {pedido.tipoEntrega === 'domicilio' && <p className="mt-1 text-sm text-texto-suave">Domicilio · {pedido.barrio}</p>}
                </div>

                {error && <p className="text-sm text-red-600">{error}</p>}

                {pedido.puedeMarcarPagado && (
                    <div className="space-y-3 border-t border-gris-claro pt-4">
                        <h4 className="text-sm font-semibold text-tinta">Marcar como pagado</h4>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="flex flex-col gap-1">
                                <label htmlFor="pedido-metodo" className="text-sm font-medium text-tinta">
                                    Método
                                </label>
                                <select
                                    id="pedido-metodo"
                                    value={metodo}
                                    onChange={(evento) => setMetodo(evento.target.value)}
                                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                                >
                                    {METODOS_PAGO.map((opcion) => (
                                        <option key={opcion.value} value={opcion.value}>
                                            {opcion.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="flex flex-col gap-1">
                                <label htmlFor="pedido-valor" className="text-sm font-medium text-tinta">
                                    Valor
                                </label>
                                <input
                                    id="pedido-valor"
                                    type="number"
                                    value={valor}
                                    onChange={(evento) => setValor(evento.target.value)}
                                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                                />
                            </div>
                        </div>
                        <Boton onClick={() => mutacionMarcarPagado.mutate()} disabled={mutacionMarcarPagado.isPending}>
                            {mutacionMarcarPagado.isPending ? 'Guardando...' : 'Marcar pagado'}
                        </Boton>
                    </div>
                )}

                {pedido.destinosCambioEstado.length > 0 && (
                    <div className="flex flex-wrap gap-2 border-t border-gris-claro pt-4">
                        {pedido.destinosCambioEstado.map((destino) => (
                            <Boton
                                key={destino.value}
                                variante="contorno"
                                disabled={mutacionCambiarEstado.isPending}
                                onClick={() => mutacionCambiarEstado.mutate(destino.value)}
                            >
                                {destino.label}
                            </Boton>
                        ))}
                    </div>
                )}
            </div>
        </Modal>
    );
}
