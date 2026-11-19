import type { ReactElement } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ChevronDown, ChevronUp } from 'lucide-react';
import Boton from '@/componentes/Boton';
import TablaPaginada from '@/componentes/TablaPaginada';
import {
    type CategoriaProducto,
    eliminarCategoriaProducto,
    obtenerCategoriasProducto,
    reordenarCategoriasProducto,
} from '../api/categoriasProducto';
import LayoutPanelNegocio from '../LayoutPanelNegocio';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCategoriasProducto({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-categorias-producto', negocioSlug],
        queryFn: () => obtenerCategoriasProducto(negocioSlug),
    });

    const mutacionEliminar = useMutation({
        mutationFn: (id: number) => eliminarCategoriaProducto(negocioSlug, id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['panel-categorias-producto', negocioSlug] }),
    });

    const mutacionReordenar = useMutation({
        mutationFn: (ids: number[]) => reordenarCategoriasProducto(negocioSlug, ids),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['panel-categorias-producto', negocioSlug] }),
    });

    function mover(categorias: CategoriaProducto[], indice: number, direccion: -1 | 1): void {
        const destino = indice + direccion;

        if (destino < 0 || destino >= categorias.length) {
            return;
        }

        const copia = [...categorias];
        [copia[indice], copia[destino]] = [copia[destino], copia[indice]];
        mutacionReordenar.mutate(copia.map((categoria) => categoria.id));
    }

    const categorias = consulta.data ?? [];

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Categorías de producto</h2>
                    <Boton onClick={() => (window.location.href = `/app/${negocioSlug}/categorias-producto/create`)}>Nueva categoría</Boton>
                </div>

                <TablaPaginada
                    columnas={[
                        {
                            clave: 'orden',
                            etiqueta: '',
                            render: (fila) => {
                                const indice = categorias.findIndex((categoria) => categoria.id === fila.id);

                                return (
                                    <div className="flex gap-1">
                                        <button
                                            type="button"
                                            aria-label="Subir"
                                            disabled={indice === 0}
                                            onClick={() => mover(categorias, indice, -1)}
                                            className="rounded-boton p-1 text-texto-suave hover:bg-fondo-panel disabled:opacity-30"
                                        >
                                            <ChevronUp size={16} strokeWidth={1.75} />
                                        </button>
                                        <button
                                            type="button"
                                            aria-label="Bajar"
                                            disabled={indice === categorias.length - 1}
                                            onClick={() => mover(categorias, indice, 1)}
                                            className="rounded-boton p-1 text-texto-suave hover:bg-fondo-panel disabled:opacity-30"
                                        >
                                            <ChevronDown size={16} strokeWidth={1.75} />
                                        </button>
                                    </div>
                                );
                            },
                        },
                        { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                        { clave: 'productos', etiqueta: 'Productos', render: (fila) => fila.productosCount },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <div className="flex gap-2">
                                    <Boton
                                        variante="contorno"
                                        onClick={() => (window.location.href = `/app/${negocioSlug}/categorias-producto/${fila.id}/edit`)}
                                    >
                                        Editar
                                    </Boton>
                                    <Boton
                                        variante="texto"
                                        disabled={mutacionEliminar.isPending}
                                        onClick={() => {
                                            if (window.confirm(`¿Eliminar la categoría "${fila.nombre}"?`)) {
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
                    filas={categorias}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no tienes categorías de producto."
                    codigoError="REF-CPR1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={1}
                    totalPaginas={1}
                    onCambiarPagina={() => {}}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
