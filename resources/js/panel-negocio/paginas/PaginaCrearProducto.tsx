import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearProducto, type DatosProducto } from '../api/productos';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioProducto from './FormularioProducto';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearProducto({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosProducto) => crearProducto(negocioSlug, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/productos`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nuevo producto</h2>
                <FormularioProducto slug={negocioSlug} enviando={mutacion.isPending} textoBoton="Crear" onGuardar={(datos) => mutacion.mutateAsync(datos)} />
            </div>
        </LayoutPanelNegocio>
    );
}
