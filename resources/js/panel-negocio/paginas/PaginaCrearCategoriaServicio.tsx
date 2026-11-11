import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearCategoriaServicio, type DatosCategoriaServicio } from '../api/categoriasServicio';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioCategoriaServicio from './FormularioCategoriaServicio';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearCategoriaServicio({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosCategoriaServicio) => crearCategoriaServicio(negocioSlug, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/categorias-servicio`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nueva categoría de servicio</h2>
                <FormularioCategoriaServicio
                    slug={negocioSlug}
                    enviando={mutacion.isPending}
                    textoBoton="Crear"
                    onGuardar={(datos) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
