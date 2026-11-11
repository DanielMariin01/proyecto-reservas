import type { ReactElement } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ChevronDown, ChevronUp } from 'lucide-react';
import Boton from '@/componentes/Boton';
import TablaPaginada from '@/componentes/TablaPaginada';
import { eliminarServicio, obtenerServicios, reordenarServicios, type Servicio } from '../api/servicios';
import LayoutPanelNegocio from '../LayoutPanelNegocio';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaServicios({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-servicios', negocioSlug],
        queryFn: () => obtenerServicios(negocioSlug),
    });

    const mutacionEliminar = useMutation({
        mutationFn: (id: number) => eliminarServicio(negocioSlug, id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['panel-servicios', negocioSlug] }),
    });

    const mutacionReordenar = useMutation({
        mutationFn: (ids: number[]) => reordenarServicios(negocioSlug, ids),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['panel-servicios', negocioSlug] }),
    });

    function mover(servicios: Servicio[], indice: number, direccion: -1 | 1): void {
        const destino = indice + direccion;

        if (destino < 0 || destino >= servicios.length) {
            return;
        }

        const copia = [...servicios];
        [copia[indice], copia[destino]] = [copia[destino], copia[indice]];
        mutacionReordenar.mutate(copia.map((servicio) => servicio.id));
    }

    const servicios = consulta.data ?? [];

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Servicios</h2>
                    <Boton onClick={() => (window.location.href = `/app/${negocioSlug}/servicios/create`)}>Nuevo servicio</Boton>
                </div>

                <TablaPaginada
                    columnas={[
                        {
                            clave: 'orden',
                            etiqueta: '',
                            render: (fila) => {
                                const indice = servicios.findIndex((servicio) => servicio.id === fila.id);

                                return (
                                    <div className="flex gap-1">
                                        <button
                                            type="button"
                                            aria-label="Subir"
                                            disabled={indice === 0}
                                            onClick={() => mover(servicios, indice, -1)}
                                            className="rounded-boton p-1 text-texto-suave hover:bg-fondo-panel disabled:opacity-30"
                                        >
                                            <ChevronUp size={16} strokeWidth={1.75} />
                                        </button>
                                        <button
                                            type="button"
                                            aria-label="Bajar"
                                            disabled={indice === servicios.length - 1}
                                            onClick={() => mover(servicios, indice, 1)}
                                            className="rounded-boton p-1 text-texto-suave hover:bg-fondo-panel disabled:opacity-30"
                                        >
                                            <ChevronDown size={16} strokeWidth={1.75} />
                                        </button>
                                    </div>
                                );
                            },
                        },
                        {
                            clave: 'foto',
                            etiqueta: '',
                            render: (fila) =>
                                fila.foto ? (
                                    <img src={fila.foto} alt="" className="h-10 w-10 rounded-boton object-cover" />
                                ) : (
                                    <div className="h-10 w-10 rounded-boton bg-gris-claro" />
                                ),
                        },
                        { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                        { clave: 'categoria', etiqueta: 'Categoría', render: (fila) => fila.categoriaServicioNombre ?? 'Sin categoría' },
                        { clave: 'duracion', etiqueta: 'Duración', render: (fila) => `${fila.duracionMinutos} min` },
                        { clave: 'precio', etiqueta: 'Precio', render: (fila) => `$${fila.precio.toLocaleString('es-CO')}` },
                        { clave: 'visible', etiqueta: 'Visible', render: (fila) => (fila.visible ? 'Sí' : 'No') },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <div className="flex gap-2">
                                    <Boton variante="contorno" onClick={() => (window.location.href = `/app/${negocioSlug}/servicios/${fila.id}/edit`)}>
                                        Editar
                                    </Boton>
                                    <Boton
                                        variante="texto"
                                        disabled={mutacionEliminar.isPending}
                                        onClick={() => {
                                            if (window.confirm(`¿Eliminar el servicio "${fila.nombre}"?`)) {
                                                mutacionEliminar.mutate(fila.id);
                                            }
                                        }}
                                    >
                                        Eliminar
                                    </Boton>
                                </div>
                            ),
                        },
                    ]}
                    filas={servicios}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no tienes servicios."
                    codigoError="REF-SER0"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={1}
                    totalPaginas={1}
                    onCambiarPagina={() => {}}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
