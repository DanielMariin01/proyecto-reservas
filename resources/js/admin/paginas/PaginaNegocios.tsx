import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import {
    cambiarEstadoNegocio,
    type DatosCambiarEstado,
    type DatosRegistrarPago,
    entrarComoNegocio,
    type Negocio,
    obtenerNegocios,
    registrarPagoSuscripcion,
} from '../api/negocios';
import TablaPaginada from '../componentes/TablaPaginada';
import LayoutAdmin from '../LayoutAdmin';
import ModalCambiarEstado from './ModalCambiarEstado';
import ModalRegistrarPago from './ModalRegistrarPago';

const CLASES_POR_ESTADO: Record<string, string> = {
    activo: 'bg-secundario text-blanco',
    vencido: 'bg-acento text-blanco',
    suspendido: 'bg-gris-claro text-tinta',
    cancelado: 'bg-tinta text-blanco',
};

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaNegocios({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const [negocioParaPago, setNegocioParaPago] = useState<Negocio | null>(null);
    const [negocioParaEstado, setNegocioParaEstado] = useState<Negocio | null>(null);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['admin-negocios', pagina],
        queryFn: () => obtenerNegocios(pagina),
    });

    const mutacionEntrar = useMutation({
        mutationFn: entrarComoNegocio,
        onSuccess: (url) => {
            window.location.href = url;
        },
    });

    const mutacionPago = useMutation({
        mutationFn: (datos: DatosRegistrarPago) => registrarPagoSuscripcion(negocioParaPago!.slug, datos),
        onSuccess: () => {
            setNegocioParaPago(null);
            queryClient.invalidateQueries({ queryKey: ['admin-negocios'] });
        },
    });

    const mutacionEstado = useMutation({
        mutationFn: (datos: DatosCambiarEstado) => cambiarEstadoNegocio(negocioParaEstado!.slug, datos),
        onSuccess: () => {
            setNegocioParaEstado(null);
            queryClient.invalidateQueries({ queryKey: ['admin-negocios'] });
        },
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Negocios</h2>
                    <Boton onClick={() => (window.location.href = '/admin/negocios/create')}>Nuevo negocio</Boton>
                </div>

                <TablaPaginada
                    columnas={[
                        { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                        { clave: 'slug', etiqueta: 'Slug', render: (fila) => fila.slug },
                        { clave: 'tipo', etiqueta: 'Tipo', render: (fila) => fila.tipoEtiqueta },
                        {
                            clave: 'estado',
                            etiqueta: 'Suscripción',
                            render: (fila) => (
                                <span className={`rounded-boton px-2 py-1 text-xs font-semibold ${CLASES_POR_ESTADO[fila.estadoSuscripcion]}`}>
                                    {fila.estadoSuscripcionEtiqueta}
                                </span>
                            ),
                        },
                        { clave: 'proximoPago', etiqueta: 'Próximo pago', render: (fila) => fila.proximaFechaPago ?? '—' },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <div className="flex flex-wrap gap-2">
                                    <Boton variante="contorno" onClick={() => (window.location.href = `/admin/negocios/${fila.slug}`)}>
                                        Ver
                                    </Boton>
                                    <Boton variante="contorno" onClick={() => (window.location.href = `/admin/negocios/${fila.slug}/edit`)}>
                                        Editar
                                    </Boton>
                                    <Boton variante="contorno" disabled={mutacionEntrar.isPending} onClick={() => mutacionEntrar.mutate(fila.slug)}>
                                        Entrar como el negocio
                                    </Boton>
                                    <Boton variante="contorno" onClick={() => setNegocioParaPago(fila)}>
                                        Registrar pago
                                    </Boton>
                                    <Boton variante="texto" onClick={() => setNegocioParaEstado(fila)}>
                                        Cambiar estado
                                    </Boton>
                                </div>
                            ),
                        },
                    ]}
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no hay negocios."
                    codigoError="REF-NEG0"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? pagina}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>

            {negocioParaPago && (
                <ModalRegistrarPago
                    enviando={mutacionPago.isPending}
                    onGuardar={(datos) => mutacionPago.mutateAsync(datos)}
                    onCerrar={() => setNegocioParaPago(null)}
                />
            )}

            {negocioParaEstado && (
                <ModalCambiarEstado
                    enviando={mutacionEstado.isPending}
                    onGuardar={(datos) => mutacionEstado.mutateAsync(datos)}
                    onCerrar={() => setNegocioParaEstado(null)}
                />
            )}
        </LayoutAdmin>
    );
}
