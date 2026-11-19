import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarCategoriaProducto, type DatosCategoriaProducto, eliminarCategoriaProducto } from '../api/categoriasProducto';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioCategoriaProducto from './FormularioCategoriaProducto';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    categoriaId: number;
    valoresIniciales: DatosCategoriaProducto;
}

export default function PaginaEditarCategoriaProducto({
    negocioNombre,
    negocioSlug,
    urlSalir,
    tokenCsrf,
    categoriaId,
    valoresIniciales,
}: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosCategoriaProducto) => actualizarCategoriaProducto(negocioSlug, categoriaId, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/categorias-producto`;
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarCategoriaProducto(negocioSlug, categoriaId),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/categorias-producto`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar categoría de producto</h2>
                    <Boton
                        variante="texto"
                        disabled={mutacionEliminar.isPending}
                        onClick={() => {
                            if (window.confirm('¿Eliminar esta categoría?')) {
                                mutacionEliminar.mutate();
                            }
                        }}
                    >
                        Eliminar
                    </Boton>
                </div>
                <FormularioCategoriaProducto
                    slug={negocioSlug}
                    valoresIniciales={valoresIniciales}
                    enviando={mutacion.isPending}
                    textoBoton="Guardar cambios"
                    onGuardar={(datos) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
