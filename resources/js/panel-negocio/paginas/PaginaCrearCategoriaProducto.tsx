import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearCategoriaProducto, type DatosCategoriaProducto } from '../api/categoriasProducto';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioCategoriaProducto from './FormularioCategoriaProducto';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearCategoriaProducto({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosCategoriaProducto) => crearCategoriaProducto(negocioSlug, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/categorias-producto`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nueva categoría de producto</h2>
                <FormularioCategoriaProducto
                    slug={negocioSlug}
                    enviando={mutacion.isPending}
                    textoBoton="Crear"
                    onGuardar={(datos) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
