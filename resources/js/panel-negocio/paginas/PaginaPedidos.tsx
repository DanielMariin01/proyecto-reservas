import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import TablaPaginada from '@/componentes/TablaPaginada';
import { obtenerPedidos, type Pedido } from '../api/pedidos';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import ModalAccionesPedido from './ModalAccionesPedido';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaPedidos({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const queryClient = useQueryClient();
    const [pedidoSeleccionado, setPedidoSeleccionado] = useState<Pedido | null>(null);

    const consulta = useQuery({
        queryKey: ['panel-pedidos', negocioSlug],
        queryFn: () => obtenerPedidos(negocioSlug),
    });

    function cerrarModal(): void {
        setPedidoSeleccionado(null);
    }

    function alTenerExito(): void {
        cerrarModal();
        queryClient.invalidateQueries({ queryKey: ['panel-pedidos', negocioSlug] });
    }

    const pedidos = consulta.data ?? [];

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Pedidos</h2>

                <TablaPaginada
                    columnas={[
                        { clave: 'fecha', etiqueta: 'Fecha', render: (fila) => fila.creadoVisible ?? '—' },
                        { clave: 'cliente', etiqueta: 'Cliente', render: (fila) => fila.clienteNombre ?? '—' },
                        {
                            clave: 'items',
                            etiqueta: 'Productos',
                            render: (fila) => fila.items.map((item) => `${item.cantidad}× ${item.nombreProducto}`).join(', '),
                        },
                        { clave: 'entrega', etiqueta: 'Entrega', render: (fila) => fila.tipoEntregaEtiqueta },
                        { clave: 'total', etiqueta: 'Total', render: (fila) => `$${fila.total.toLocaleString('es-CO')}` },
                        { clave: 'estado', etiqueta: 'Estado', render: (fila) => fila.estadoEtiqueta },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) =>
                                fila.puedeMarcarPagado || fila.destinosCambioEstado.length > 0 ? (
                                    <Boton variante="texto" onClick={() => setPedidoSeleccionado(fila)}>
                                        Acciones
                                    </Boton>
                                ) : null,
                        },
                    ]}
                    filas={pedidos}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no hay pedidos."
                    codigoError="REF-PED1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={1}
                    totalPaginas={1}
                    onCambiarPagina={() => {}}
                />
            </div>

            {pedidoSeleccionado && (
                <ModalAccionesPedido slug={negocioSlug} pedido={pedidoSeleccionado} onExito={alTenerExito} onCerrar={cerrarModal} />
            )}
        </LayoutPanelNegocio>
    );
}
