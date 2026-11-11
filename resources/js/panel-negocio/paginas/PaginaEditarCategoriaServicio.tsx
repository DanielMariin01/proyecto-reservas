import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarCategoriaServicio, type DatosCategoriaServicio, eliminarCategoriaServicio } from '../api/categoriasServicio';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioCategoriaServicio from './FormularioCategoriaServicio';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    categoriaId: number;
    valoresIniciales: DatosCategoriaServicio;
}

export default function PaginaEditarCategoriaServicio({
    negocioNombre,
    negocioSlug,
    urlSalir,
    tokenCsrf,
    categoriaId,
    valoresIniciales,
}: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosCategoriaServicio) => actualizarCategoriaServicio(negocioSlug, categoriaId, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/categorias-servicio`;
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarCategoriaServicio(negocioSlug, categoriaId),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/categorias-servicio`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar categoría de servicio</h2>
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
                <FormularioCategoriaServicio
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
